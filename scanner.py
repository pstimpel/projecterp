import cv2
import numpy as np
import pyzbar.pyzbar as pyzbar
import leds
import select, socket, sys, queue
import syslogger

from socket import SHUT_RDWR
import time
myleds = leds.Leds()
log = syslogger.Syslogger()

#cap = cv2.VideoCapture(0)
#font = cv2.FONT_HERSHEY_PLAIN

server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
server.setblocking(0)
server.bind(('localhost', 50000))
server.listen(5)
inputs = [server]
outputs = []
message_queues = {}

log.log('started', 'scanner')

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
                if data == b'scan\r\n':
                    message_queues[s].put(b'scanning\r\n')
                elif data == b'exit\r\n':
                    message_queues[s].put(b'bye\r\n')
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
            next_msg = message_queues[s].get_nowait()
        except queue.Empty:
            outputs.remove(s)
        else:
            s.send(next_msg)
            print(next_msg)
        if next_msg == b'bye\r\n':
            try:
                inputs.remove(s)
                outputs.remove(s)
                
                s.shutdown(SHUT_RDWR)
                del message_queues[s]
                s.close()
            except Exception:
                pass
        elif next_msg == b'scanning\r\n':
            myleds.show(255,255,255,31) #white
            cap = cv2.VideoCapture(0)
            count=0
            runner = True
            while runner:
                count=count+1
                _, frame = cap.read()

                decodedObjects = pyzbar.decode(frame)
                for obj in decodedObjects:
                    print(obj.data)
                    s.send(b'data:'+obj.data+b'\r\n')
                    cap.release()
                    next_msg=''
                    runner=False
                    myleds.show(0,255,0,15)
                    time.sleep(1)
                    myleds.show(0,0,0,0)
                    break
                if count > 100:
                    s.send(b'scan failed, timeout\r\n')
                    cap.release()
                    next_msg=''
                    runner=False
                    myleds.show(255,0,0,15,True)
                    time.sleep(2)
                    myleds.show(0,0,0,0)
                    break
            
        

        next_msg=''
    for s in exceptional:
        inputs.remove(s)
        if s in outputs:
            outputs.remove(s)
        s.close()
        del message_queues[s]
