import socket
import leds
import mail
import time
import requests
import json
import pygame
import os
import syslogger
import json

myleds = leds.Leds()
mymail = mail.Mail()
log = syslogger.Syslogger()

host_ip, server_port_listen, server_port_talk = "127.0.0.1", 50002, 50001

import RPi.GPIO as GPIO
GPIO.setmode(GPIO.BCM)
GPIO.setup(13, GPIO.IN)


openai_apikey="YOUR OPENAI API KEY"

def checkgpt(command):

        commandplain = '''Prüfe folgenden Eingabe in folgender Reihenfolge.

        Prüfe zuerst ob die Eingabe nur eine Zahl darstellen soll. Erlaubt sind INT oder FLOAT.
        Wenn eine Umwandlung möglich ist, gib die erkannte Zahl aus und beende die Abarbeitung dieses Befehls.

        Wenn keine Umwandlung möglich ist, prüfe die Eingabe nach folgenden weiteren Regeln.

        Prüfe auf Rechtschreibprüfung, nicht Grammatik. Prüfe den Satz dabei mit einem elektronischen bzw. elektrotechnischen Hintergrund.

        Wenn die Rechtschreibung falsch ist, korrigiere diese. Wenn die Eingabe in Ordnung ist, nimm die Eingabe für die weitere Verarbeitung.

        Wenn du Zahlen im Textformat findest, ersetze diese in der Ausgabe ebenfalls mit dem entsprechenden Zahlenwert.
        Dabei kannst du Phrasen wie ein Viertel in die Zahl umwandeln. z.B. 0,25.

        Gib keine Anmerkungen aus. Gib auch das Wort Ausgabe nicht aus. Wenn du nichts veränderst, gib die Eingabe aus ohne weitere Anmerkungen.
                                                                                                                                                                                 
        Die Eingabe:  '''


        headers = {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + openai_apikey,
        }

        json_data = {
            'model': 'gpt-4o-mini',
            'messages': [
                {
                    'role': 'user',
                    'content': '' + commandplain + " " + command,
                },
            ],
        }

        response = requests.post('https://api.openai.com/v1/chat/completions', headers=headers, json=json_data)

        # Note: json_data will not be serialized by requests
        # exactly as it was in the original request.
        #data = '{\n        "model": "gpt-4o-mini",\n        "messages": [\n            {\n                "role": "user",\n                "content": "mytext"\n       $        #response = requests.post('https://api.openai.com/v1/chat/completions', headers=headers, data=data)

        if response.status_code == 200:
            response_data = response.json()
            #print(json.dumps(response_data, indent=2))  # Inspect the full response

            # Access specific parts of the response
            choices = response_data.get("choices", [])
            if choices:
                generated_text = choices[0].get("message", {}).get("content", "")
                log.log("got " + command + ", and got back " + generated_text, 'basictalk.checkgpt')
                return generated_text
            else:
                log.log("got " + command + ", but now answer", 'basictalk.checkgpt')
                return command
        else:
            #print(f"Error: {response.status_code}")
            #print(response.text)  # Additional error information
            log.log("got error " + response.text, 'basictalk.checkgpt')
            return command


def listen():
    receivedout=""
    try:
        tcp_client = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        # Establish connection to TCP server and exchange data
        tcp_client.connect((host_ip, server_port_listen))
        tcp_client.sendall('listen\r\n'.encode())

        # Read data from the TCP server and close the connection
        while True:
            received = tcp_client.recv(1024)
            if received.decode()[0:12]=="transcribed:":
                break
            else:
                print(received.decode())

        print(received.decode())

        receivedout=received.decode()[12:len(received.decode())]
        print(receivedout)
    except Exception as err:
        print(repr(err))
        log.log(str(repr(err)), 'basictalk.listen')
    finally:
        tcp_client.close()

    return receivedout

def deletefile(file):
    try:
        if os.path.isfile(file):
            os.remove(file) 
    except Exception as err:
        print(repr(err))
        log.log(str(repr(err)), 'basictalk.deletefile')
    finally:
        pass

def talk(text):
    
    deletefile('audiofiles/output.mp3')

    try:
        tcp_client2 = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        tcp_client2.connect((host_ip, server_port_talk))
        tcp_client2.sendall(b'talk:'+bytes(text, 'utf-8')+b'\r\n')
        
        while True:
            received = tcp_client2.recv(1024)
            if received.decode()[0:5]=="talk:":
                break
            else:
                print(received.decode())
    except Exception as err:
        print(repr(err))
        log.log(str(repr(err)), 'basictalk.talk.socket')
    finally:
        tcp_client2.close()
    
    
    print('proceeding with blue and sleep')

    myleds.show(0,0,255,25) #blue full
    time.sleep(2)
    print('proceeding with playing')
    
    try:
        if os.path.isfile("audiofiles/output.mp3"):
            pygame.mixer.init()
            pygame.mixer.music.load("audiofiles/output.mp3")
            pygame.mixer.music.set_volume(1.0)
            pygame.mixer.music.play()
            while pygame.mixer.music.get_busy() == True:
                time.sleep(0.1)
                print('. ',end='')
                    
            print('played')
        else:
            print('audiofiles/output.mp3 does not exist')                
    except Exception as err:
        print(repr(err))
        log.log(str(repr(err)), 'basictalk.listen.playing')
    finally:
        pass

    deletefile('audiofiles/output.mp3')
    
    print('exit talk')

log.log('started', 'basictalk')

while True:
    enterloop=False
    myleds.wakeup()
    myleds.show(0,0,0,0) #black
    while True:
        if GPIO.input(13) == 0:
            time.sleep(0.1) 
            #print("low")
        else:
            enterloop=True
            break
    jsondata =  {
        "init": True,
    }       
    #print("Go")
    while enterloop:   
        try:
            command = listen().replace("\r","").replace("\n","")

            command = checkgpt(command)

            if command[0:8].lower()=="bestelle":
                print("should write mail with subject bestelle:")
                mymail.send("zu bestellen", command)
                # mach nen einfachen Request und schreibe den Bestelltext ins Backlog

                jsondata['action']=command[8:]
                jsondata = json.dumps(jsondata, indent=2)
                print(jsondata)
            
                newHeaders = {'Content-type': 'application/json', 'Accept': 'text/plain'}

                response = requests.post(url="http://192.168.0.6:81/storagelocation/talk.php",
                                    data=jsondata,
                                    headers=newHeaders)
                enterloop=False
                break

            if command[0:5].lower()=="notiz":
                print("should write mail with subject Notiz:")
                mymail.send("Notiz", command)
                enterloop=False
                break

            if "followupworkflowstep" in jsondata:
                jsondata['action']=command
                jsondata['workflowstep'] = jsondata['followupworkflowstep']
            else:
                jsondata =  {
                    "action": command,
                }

            jsondata = json.dumps(jsondata, indent=2)
            print(jsondata)
            
            newHeaders = {'Content-type': 'application/json', 'Accept': 'text/plain'}

            response = requests.post(url="http://192.168.0.6:81/storagelocation/talk.php",
                                    data=jsondata,
                                    headers=newHeaders)

            print("Status code: ", response.status_code)
            print(response.text)
            response_Json = response.json()
            print(response_Json)
            

            if "talk" in response_Json:
                print("Talk") 
            
                talk(response_Json['talk'])
                myleds.show(0,0,0,0) #black
                #time.sleep(2) 
            else:
                talk("Keine Rückmeldung von der Schnittstelle")
                
            if "nextaction" in response_Json:
                print("nextaction") 
                if(response_Json['nextaction']=="exit"):
                    print("Exiting")
                    enterloop=False
                    break
            jsondata = response_Json
        except Exception as err:
            print(repr(err))
            log.log(str(repr(err)), 'basictalk.listen.loop')
        
    myleds.show(0,0,0,0) #black
    #exit()

