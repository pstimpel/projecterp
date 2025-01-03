apt-get update
apt-get upgrade

apt-get update

apt-get install python3-opencv git
sudo apt install ffmpeg

apt-get install libqt4-test python3-sip python3-pyqt5 libqtgui4 libjasper-dev libatlas-base-dev -y

apt-get install python3-pip

pip3 install opencv-contrib-python==4.1.0.25

modprobe bcm2835-v4l2

apt-get install libsdl2-mixer-2.0-0

sudo apt-get install -y build-essential libzbar-dev
#pip3 install zbar
sudo apt-get install zbar-tools
pip3 install pyzbar

pip3 install imutils

pip3 install argparse
pip3 install pyaudio
pip3 install pygame

sudo apt-get install python-pyaudio python3-pyaudio
sudo apt install espeak

pip3 install google-cloud-texttospeech
sudo apt-get install rpi.gpio
pip3 install pydub 

pip3 install SpeechRecognition

git clone https://github.com/respeaker/seeed-voicecard.git
cd seeed-voicecard
sudo ./install.sh --compat-kernel
reboot

cd /home/pi
git clone https://github.com/respeaker/4mics_hat.git
cd /home/pi/4mics_hat
sudo apt install python-virtualenv         
virtualenv --system-site-packages ~/env     
source ~/env/bin/activate                  
pip install spidev gpiozero      

sudo pip3 install pyaudio



https://steelkiwi.com/blog/working-tcp-sockets/
https://github.com/AbdallahHemdan/QR-Scanner/blob/master/QR%20from%20WebCam/QR_WebCam.py
https://wiki.seeedstudio.com/ReSpeaker_4_Mic_Array_for_Raspberry_Pi/

https://www.seeedstudio.com/ReSpeaker-USB-Mic-Array-p-4247.html

https://maker.pro/raspberry-pi/projects/speech-recognition-using-google-speech-api-and-python

https://www.piddlerintheroot.com/voice-recognition/



https://cloud.google.com/text-to-speech

export GOOGLE_APPLICATION_CREDENTIALS="/home/pi/google.json"

liximomo.sftp
pip3 install --upgrade google-cloud-speech




https://www.techbeamers.com/python-tutorial-write-tcp-server/
https://stackoverflow.com/questions/26768213/python-sockets-sending-a-packet-to-a-server-and-waiting-for-a-response


if AI is in use, get an OPENAI API key and add it in basictalk.py. If no AI is used, just comment out 

"command = checkgpt(command)"

by adding a # in front of it

