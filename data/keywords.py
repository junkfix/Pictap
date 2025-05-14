'''
create venv
python3 -m venv ~/.local --system-site-packages
make sure echo $PATH has ~/.local/bin or edit in ~/.profile

sudo apt install libhdf5-dev
pip install h5py Pillow flatbuffers==23.5.26 tensorflow keras

for mysql:
pip install mysql-connector-python

for postgresql:
sudo apt install libpq-dev
pip install --upgrade psycopg2-binary
or
pip install psycopg2


Usage: python keywords.py /somepath/pictap_config.php

'''
import os
import sys
import time
import tempfile
import json

def jsonfile(file_path):
    try:
        with open(file_path, 'r') as file:
            data = json.load(file)
            return data
    except:
        print(f"Error decoding JSON in {file_path}")
        return None
        
def multirun(lock_file):
    try:
        try:
            os.remove(lock_file)
        except:
            pass
        lock_fd = os.open(lock_file, os.O_CREAT | os.O_EXCL | os.O_RDWR)
        return lock_fd
    except:
        #print("Another instance is already running. Exiting.")
        sys.exit(1)

def is_image(f):
    x = {'.jpg', '.jpeg', '.png', '.bmp', '.gif', '.tiff', '.tif', '.webp'}
    return any(f.lower().endswith(ext) for ext in x)

def save_db(cursor, r, tagtable, config):
    
    ph = '%s'    
    b = ''
    e = ';'
    if config['db_type'] == 'pgsql':
        e = ' ON CONFLICT DO NOTHING;'
    else:
        if config['db_type'] == 'sqlite':
            ph = '?'
            b = 'OR '
        b = 'IGNORE ' + b
    b = 'INSERT ' + b

    for kw, t in tagtable.items():
        q = f"{b}INTO {config['db_prefix']}tags (tag, cat) VALUES({ph}, {ph}) {e}"
        cursor.execute(q, (kw, t))
        
        q = f"{b}INTO {config['db_prefix']}tagfiles (fileid, tagid) VALUES ({ph}, (SELECT tagid FROM {config['db_prefix']}tags WHERE lower(tag) = lower({ph}))){e}"
        cursor.execute(q, (r['fileid'], kw));
        
    q = f"UPDATE {config['db_prefix']}files SET kw = 1 WHERE fileid = {ph};"
    cursor.execute(q,(r['fileid'],))
    cursor.connection.commit()
    
def main():

    if len(sys.argv) != 2:
        print("Usage: python keywords.py /somepath/pictap_config.php")
        sys.exit(1)

    configfile = sys.argv[1]
    config = jsonfile(configfile)

    if not config:
        print("json error "+ configfile)
        exit()
    config = config[1]
    
    lock_file = config['path_data'] + "/sql.lock"
    lock_fd = multirun(lock_file)

    kwdfile = config['path_data'] + "/keywords.json"
    z = jsonfile(kwdfile)
        
    if not z:
        print("json error ",kwdfile)
        exit()
    
    conn = None
    cursor = None
    
    conn_args = {
        'host': config['db_host'],
        'user': config['db_user'],
        'password': config['db_pass']
    }
    port = config.get('port')
    if port and int(port) != 0:
        conn_args['port'] = int(port)
    
    if config['db_type'] == 'pgsql':
        import psycopg2
        import psycopg2.extras
        conn_args['dbname'] = config['db_name']
        conn = psycopg2.connect(**conn_args)
        cursor = conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor)

    elif config['db_type'] == 'mysql':
        import mysql.connector
        conn_args['database'] = config['db_name']
        conn = mysql.connector.connect(**conn_args)
        cursor = conn.cursor(dictionary=True)

    else:  # SQLite
        import sqlite3
        conn = sqlite3.connect(config['db_file'])
        conn.row_factory = sqlite3.Row
        conn.execute("PRAGMA foreign_keys = ON")
        cursor = conn.cursor()
    

    folder = config['path_pictures']
    
    try:
        cursor.execute(f"SELECT f.fileid, d.dir, f.file FROM {config['db_prefix']}dirs d JOIN {config['db_prefix']}files f ON d.dirid = f.dirid WHERE f.kw = 0")
        results = cursor.fetchall()
    except Exception as e:
        print(f"Error: {e}")
        exit()

    if not results:
        print("no results")
        exit();

    from tensorflow.keras.applications import NASNetMobile
    from tensorflow.keras.preprocessing import image
    from tensorflow.keras.applications.nasnet import preprocess_input, decode_predictions
    import numpy as np
        
    try:
        model = NASNetMobile(input_shape=(224, 224, 3), include_top=True, weights='imagenet')
    except Exception as e:
        print(f"Error: {e}")
        exit()
        
    #rawdata = {}
    
    for r in results:
        tagtable = {}
        sep = '' if r['dir'] == '' else '/'
        img_path = folder + sep + r['dir'] + '/' + r['file']
        print("res:", img_path)
        
        #rawdata[img_path] = []
        
        if not is_image(r['file']):
            save_db(cursor, r, tagtable, config)
            continue
        
        try:
            img = image.load_img(img_path, target_size=(224, 224))
        except Exception as e:
            print(f"Error loading the image: {e}")
            save_db(cursor, r, tagtable, config)
            continue

        img = image.img_to_array(img)
        img = np.expand_dims(img, axis=0)
        img = preprocess_input(img)

        try:
            predictions = model.predict(img, verbose=0)
        except Exception as e:
            print(f"Error making predictions: {e}")
            #save_db(cursor, r, tagtable, config)
            continue

        foundtags = decode_predictions(predictions)

        labels = []

        for i, (imagenet_id, label, score) in enumerate(foundtags[0]):
            label = label.lower().replace("_", " ")
            score = score * 100
            original_label = label
            while label in z['tags'] and "i" in z['tags'][label]:
                label = z['tags'][label]["i"]
            
            tag_row = z['tags'].get(label,{})
            if "t" not in tag_row:
                tag_row["t"] = label
            if "o" not in tag_row:
                tag_row["o"] = 0
            if "p" not in tag_row:
                tag_row["p"] = 7
            
            if label in z['skip'] or score < tag_row["p"]:
                continue
            tags = list(set([original_label, label, tag_row["t"]]))
            #tags = list(set([label, tag_row["t"]]))
            cats = tag_row.get("c", [])

            label_info = {
                "t": tags,
                "c": cats,
                "p": round(score),
                "o": tag_row.get("o", 0),
            }

            labels.append(label_info)

        sortedtags = sorted(labels, key=lambda x: x.get("o", 0), reverse=True)[:5]
        
        #rawdata[img_path] = [{"t": label["t"], "c": label["c"], "p": label["p"]} for label in sortedtags]
                
        for label_info in sortedtags:
            print(f"p: {label_info['p']} - t: {label_info['t']} - c: {label_info['c']}")
            
            for c in label_info['c']:
                c = c[0].upper() + c[1:]
                tagtable[c]=1
            for c in label_info['t']:
                c = c[0].upper() + c[1:]
                tagtable[c]=0
        
        save_db(cursor, r, tagtable, config)
        
    conn.close()

    os.close(lock_fd)


if __name__ == "__main__":
    main()






