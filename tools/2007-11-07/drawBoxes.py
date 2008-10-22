#!/usr/bin/python
import getopt
import os
import sys
import tempfile

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], 'i:o:b:')
    except getopt.GetoptError:
        # print help information and exit:
        usage()
        sys.exit(2)
    #endtry

    inputImage = None
    outputImage = None
    boxFile = None
    for option, value in opts:
        if option == '-i':
            inputImage = value
        elif option == '-o':
            outputImage = value
        elif option == '-b':
            boxFile = value
        #endif
    #endfor

    if not inputImage or not outputImage or not boxFile:
        usage()
        sys.exit(2)
    #endif

    generate(inputImage, outputImage, boxFile)
#enddef

def generate(inputImage, outputImage, boxFile):
    height = findImageHeight(inputImage)
    tempFile = tempfile.NamedTemporaryFile()
    tempFileName = tempFile.name
    f = open(boxFile, 'r')

    for line in f:
        (char, left, bottom, right, top) = line.split()
        if char.find('"') >= 0:
            quotedChar = "'" + char + "'"
        else:
            quotedChar = '"' + char + '"'
        
        left = int(left)
        bottom = height - int(bottom)
        right = int(right)
        top = height - int(top)
        tempFile.write('rectangle ' + `left` + ',' + `top` + ' ' + \
                           `right` + ',' + `bottom` + '\n')
        textX = (left + right) / 2 - 5
        textY = bottom + 15
        tempFile.write('text ' + `textX` + ',' + `textY` + ' ' + quotedChar\
                           + '\n')
    #endfor
    tempFile.flush()
    command = 'convert -font courier -stroke green -fill transparent -draw "@'\
        + tempFileName + '" ' + inputImage + ' ' + outputImage
    os.system(command)
    tempFile.close()
    f.close()
    return
#enddef

def findImageHeight(image):
    f = os.popen('identify -format "%h" ' + image)
    height = int(f.readline())
    f.close()
    return height
#enddef

def usage():
  print """Usage: drawBoxes.py -i <input image>
                    -o <output image>
                    -b <box file>"""
#enddef

if __name__ == "__main__":
    main()
