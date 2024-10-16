from google.cloud import texttospeech
#import os //not needed
import pygame
import select, socket, sys, queue
from socket import SHUT_RDWR
import leds
import time
import syslogger 

log = syslogger.Syslogger()
myleds = leds.Leds()
# Instantiates a client
client = texttospeech.TextToSpeechClient()

server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
server.setblocking(0)
server.bind(('localhost', 50001))
server.listen(5)
inputs = [server]
outputs = []
message_queues = {}

log.log('started', 'talkgoogle')

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
                print(data.decode('UTF-8')[0:5])
                if data.decode('UTF-8')[0:5] == 'talk:':
                    print('talking')
                    message_queues[s].put(data)
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
        elif next_msg==b'wiederholen\r\n':
            pygame.mixer.init()
            pygame.mixer.music.load("audiofiles/output.mp3")
            pygame.mixer.music.set_volume(1.0)
            pygame.mixer.music.play()
            print('played')

            while pygame.mixer.music.get_busy() == True:
                pass           
           
            s.send(next_msg)
            print(next_msg)    
            
        elif (type(next_msg) is bytes and next_msg.decode('UTF-8')[0:5] == 'talk:') or (type(next_msg) is str and next_msg[0:5] == 'talk:'):
            try:
                myleds.show(255,0,255,25,True)
                print('talking')
                # Set the text input to be synthesized
                outtext=''
                if type(next_msg) is bytes:
                    outtext=next_msg.decode('UTF-8')
                else: 
                    outtext=next_msg

                outtext=outtext[5:len(outtext)]
                print(outtext)
                
                synthesis_input = texttospeech.SynthesisInput(text=outtext)
                print('prepare')
                # Build the voice request, select the language code ("en-US") and the ssml
                # voice gender ("neutral")
                #TODO: change language code
                voice = texttospeech.VoiceSelectionParams(
                    language_code="de-DE", 
                    name="de-DE-Standard-B",
                    ssml_gender=texttospeech.SsmlVoiceGender.MALE
                )
                print('options')

                # Select the type of audio file you want returned
                audio_config = texttospeech.AudioConfig(
                    audio_encoding=texttospeech.AudioEncoding.MP3
                )
                print('settings')

                # Perform the text-to-speech request on the text input with the selected
                # voice parameters and audio file type
                myleds.show(255,0,0,25,True)
                response = client.synthesize_speech(
                    input=synthesis_input, voice=voice, audio_config=audio_config
                )
                print('get')

                # The response's audio_content is binary.
                with open("audiofiles/output.mp3", "wb") as out:
                    # Write the response to the output file.
                    out.write(response.audio_content)
                    print('Audio content written to file "audiofiles/output.mp3"')
                    print('stored')
                myleds.show(128,128,128,5,True)

                #pygame.mixer.init()
                #pygame.mixer.music.load("audiofiles/output.mp3")
                #pygame.mixer.music.set_volume(1.0)
                #pygame.mixer.music.play()
                
                #print('played')

                #while pygame.mixer.music.get_busy() == True:
                #    pass
                #    time.sleep(1)
                #    print('. ', end = '')         
            
                s.send(next_msg)
                print(next_msg)
                #myleds.show(0,0,0,0)
            except Exception:
                pass

        next_msg=''
    for s in exceptional:
        inputs.remove(s)
        if s in outputs:
            outputs.remove(s)
        s.close()
        del message_queues[s]

