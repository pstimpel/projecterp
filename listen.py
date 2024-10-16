import select, socket, sys, queue
from socket import SHUT_RDWR

import speech_recognition as sr
import leds
from pydub import AudioSegment
import time
import syslogger

myleds = leds.Leds()
log = syslogger.Syslogger()

server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
server.setblocking(0)
server.bind(('localhost', 50002))
server.listen(5)
inputs = [server]
outputs = []
message_queues = {}
r = sr.Recognizer()
speechin = sr.Microphone(device_index=0)



def transcribe(filename):
    host_ip, server_port_transcriber = "127.0.0.1", 50003
    tcp_client = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    tcp_client.connect((host_ip, server_port_transcriber))
    sendstring='transcribe:'+filename+'\r\n'
    tcp_client.sendall(sendstring.encode())
    while True:
        received = tcp_client.recv(1024)
        if received.decode()[0:12]=="transcribed:":
            break
        else:
            print(received.decode())

    print(received.decode())

    receivedout=received.decode()[12:len(received.decode())]
    print(receivedout)
    return receivedout

log.log('started', 'listen')

while inputs:
    readable, writable, exceptional = select.select(
        inputs, outputs, inputs)
    for s in readable:
        if s is server:
            connection, client_address = s.accept()
            connection.setblocking(0)
            inputs.append(connection)
            message_queues[connection] = queue.Queue()
        else:
            data = s.recv(1024)
            if data:
                if data == b'listen\r\n':
                    message_queues[s].put(b'listen\r\n')
                else:
                    message_queues[s].put(data)

                if s not in outputs:
                    outputs.append(s)
            else:
                if s in outputs:
                    outputs.remove(s)
                inputs.remove(s)
                s.close()
                del message_queues[s]

    for s in writable:
        try:
            try:
                next_msg = message_queues[s].get_nowait()
            except queue.Empty:
                outputs.remove(s)
            else:
                s.send(next_msg)
                print(next_msg)
        except Exception:
            pass        
        if next_msg == b'bye\r\n':
            try:
                inputs.remove(s)
                outputs.remove(s)
                
                s.shutdown(SHUT_RDWR)
                del message_queues[s]
                s.close()
            except Exception:
                pass
        elif next_msg==b'listen\r\n':
            
            try:
                myleds.show(0,0,255,25,True) #darkblue
                
                with speechin as source:
                    print("say something!…")
                    audio = r.adjust_for_ambient_noise(source)
                    myleds.show(0,255,0,25) #green
                    audio = r.listen(source)

                myleds.show(0,0,255,25, True) #darkblue
                with open('audiofiles/temp.wav', 'wb') as file:
                    wav_data = audio.get_wav_data(convert_rate = 16000)
                    file.write(wav_data)

                sound = AudioSegment.from_wav("audiofiles/temp.wav")
                sound = sound.set_channels(1)
                sound.export("audiofiles/temp2.wav", format="wav")
                
                myleds.show(255,0,0,25, True) #red
                transcribed=transcribe('audiofiles/temp2.wav')
                print(transcribed)
                myleds.show(255,255,0,25, True) #yellow
                time.sleep(3)            
                s.send(b'transcribed:' + bytes(transcribed, 'utf-8') + b'\r\n')
                print(next_msg)    
                myleds.show(0,0,0,0) #green
            except Exception:
                pass
        
        next_msg=''
    for s in exceptional:
        inputs.remove(s)
        if s in outputs:
            outputs.remove(s)
        s.close()
        del message_queues[s]









