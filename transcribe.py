import select, socket, sys, queue
from socket import SHUT_RDWR
import syslogger

log = syslogger.Syslogger()

server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
server.setblocking(0)
server.bind(('localhost', 50003))
server.listen(5)
inputs = [server]
outputs = []
message_queues = {}

def transcribe_file(speech_file):
    speech_fileclean = speech_file.replace('\n', '').replace('\r', '')
    #print("->"+speech_fileclean+"<-")

    import os.path

    if os.path.isfile(speech_fileclean):

        """Transcribe the given audio file."""
        #print("1")
        from google.cloud import speech
        import io
        #print("2")
        
        client = speech.SpeechClient()
        #print("3")
        #print(speech_fileclean)
        with io.open(speech_fileclean, "rb") as audio_file:
            content = audio_file.read()

        #print("4")
        audio = speech.RecognitionAudio(content=content)
        #TODO: change language code
        config = speech.RecognitionConfig(
            encoding=speech.RecognitionConfig.AudioEncoding.LINEAR16,
            sample_rate_hertz=16000,
            language_code="de-DE",
        )
        #print("5")
        response = client.recognize(config=config, audio=audio)
        #print("6")
        transcribed=u""
        for result in response.results:
            # The first alternative is the most likely one for this portion.
            #print(u"Transcript: {}".format(result.alternatives[0].transcript))
            #print(result.alternatives[0].transcript)
            #print(transcribed)
            transcribed = transcribed + result.alternatives[0].transcript
            #print(transcribed)
            log.log(str(result.alternatives[0].transcript), 'transcribe.transcribe_file')



        #print("done with google")
        return transcribed
    else:
        log.log('transcribe failed', 'transcribe.transcribe_file')
        return 'err:err'

log.log('started', 'transcribe')

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
                print(data.decode('UTF-8')[0:11])
                if data.decode('UTF-8')[0:11] == 'transcribe:':
                    # print('talking')
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
        elif (type(next_msg) is bytes and next_msg.decode('UTF-8')[0:11] == 'transcribe:') or (type(next_msg) is str and next_msg[0:11] == 'transcribe:'):
            try:
                # print('talking')
                # Set the text input to be synthesized
                outtext=''
                if type(next_msg) is bytes:
                    outtext=next_msg.decode('UTF-8')
                else: 
                    outtext=next_msg

                outtext=outtext[11:len(outtext)]
                #print(outtext + 'temp2.wav')
                
                transcribed=transcribe_file(outtext)
                #print("got " + transcribed)
                
                s.send(b'transcribed:' + bytes(transcribed, 'utf-8') + b'\r\n')
                #print("sent")
                next_msg=''
                #print(next_msg)
               
            except Exception:
                pass

        next_msg=''
    for s in exceptional:
        inputs.remove(s)
        if s in outputs:
            outputs.remove(s)
        s.close()
        del message_queues[s]

