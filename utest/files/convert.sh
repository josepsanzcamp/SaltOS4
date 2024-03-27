#!/bin/bash

pdftoppm -r 300 -png lorem.pdf lorem
mv lorem-1.png lorem.png
convert lorem.png image.pdf

pdftoppm -r 300 -png multipages.ori.pdf multipages

convert multipages-2.png -rotate 90 multipages-2-rot.png
convert multipages-3.png -rotate 180 multipages-3-rot.png
convert multipages-4.png -rotate 270 multipages-4-rot.png

mv multipages-2-rot.png multipages-2.png
mv multipages-3-rot.png multipages-3.png
mv multipages-4-rot.png multipages-4.png

convert multipages-*.png multipages.pdf
