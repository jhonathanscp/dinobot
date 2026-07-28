import urllib.request
import json
import os

url = 'http://localhost:8080/admin/users'
token = 'seu_admin_token_aqui'

data = {
    'name': 'bot',
    'token': 'seu_token_aqui'
}

data_json = json.dumps(data).encode('utf-8')

for t in [token, token + '\r']:
    req = urllib.request.Request(url, data=data_json, headers={
        'Authorization': t,
        'Content-Type': 'application/json'
    }, method='POST')
    
    try:
        response = urllib.request.urlopen(req)
        print(f"Success with token {repr(t)}! Response: {response.read().decode('utf-8')}")
        break
    except Exception as e:
        print(f"Failed with token {repr(t)}: {e}")
        try:
            print(e.read().decode('utf-8'))
        except:
            pass
