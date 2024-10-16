import socket
import leds
import mail
import time
import requests
import json
import pygame
import os
import syslogger

myleds = leds.Leds()
mymail = mail.Mail()
log = syslogger.Syslogger()

host_ip, server_port_listen, server_port_talk = "127.0.0.1", 50002, 50001

import RPi.GPIO as GPIO
GPIO.setmode(GPIO.BCM)
GPIO.setup(13, GPIO.IN)


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

            if command[0:8]=="bestelle":
                print("should write mail with subject bestelle:")
                mymail.send("zu bestellen", command)
                enterloop=False
                break

            if command[0:5]=="Notiz":
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
            # TODO: change API HOST
            response = requests.post(url="http://APIHOST/storagelocation/talk.php",
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

