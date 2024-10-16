#!/bin/bash

sudo mkdir /home/pi/audiofiles
sudo mount /dev/sda1 /home/pi/audiofiles -o umask=000


export GOOGLE_APPLICATION_CREDENTIALS="/home/pi/google.json"

python3 scanner.py & # > /home/pi/scanner.log 2> /home/pi/error.log &
sleep 2
echo scanner started

python3 listen.py & # > /home/pi/listener.log 2> /home/pi/error.log &
sleep 5
echo listener started


python3 talkgoogle.py & # > /home/pi/talkgoogle.log 2> /home/pi/error.log &
sleep 2
echo talk started

python3 transcribe.py & # > /home/pi/talkgoogle.log 2> /home/pi/error.log &
sleep 2
echo transcribe started

python3 basictalk.py & # > /home/pi/basictalk.log 2> /home/pi/error.log &
sleep 5
echo havoc ready

