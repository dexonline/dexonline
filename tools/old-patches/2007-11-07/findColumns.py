#!/usr/bin/env python

import getopt
import gtk
import os
import sys

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], 'i:')
    except getopt.GetoptError:
        # print help information and exit:
        usage()
        sys.exit(2)
    #endtry

    inputImage = None
    for option, value in opts:
        if option == '-i':
            inputImage = value
        #endif
    #endfor

    if not inputImage:
        usage()
        sys.exit(2)
    #endif

    pixbuf = gtk.gdk.pixbuf_new_from_file(inputImage)
    height = pixbuf.get_height()
    width = pixbuf.get_width()

    pixels = pixbuf.get_pixels_array()
    divider = findDivider(pixels)
    header = findHeader(pixels, inputImage)

    col1Name = inputImage.replace('.', '-1.')
    col2Name = inputImage.replace('.', '-2.')

    extractZone(0, header, divider, height - header, inputImage, col1Name)
    extractZone(divider, header, width - divider, height - header, inputImage,
                col2Name)
#enddef

def usage():
  print 'Usage: findColumns.py -i <input image>'
#enddef

def findDivider(pixels):
    width = len(pixels[0])
    height = len(pixels)

    # Look in the middle 10% and return the column with the fewest set pixels.
    low = width * 45 // 100
    high = width * 55 // 100
    bestCol = -1
    bestNumPixels = height + 1

    for col in range(low, high):
        numPixels = 0
        for i in range (height):
            if isBlack(pixels[i][col]):
                numPixels += 1
            #endif
        #endfor
        if numPixels <= bestNumPixels:
            bestCol = col
            bestNumPixels = numPixels
        #endif
    #endfor
    return bestCol
#enddef

def findHeader(pixels, inputImage):
    # Find a horizontal line somewhere in the top 15% of the page
    width = len(pixels[0])
    height = len(pixels)

    high = width * 15 // 100
    rowTotals = []

    for row in range(high):
        numPixels = 0
        for col in range (width):
            if isBlack(pixels[row][col]):
                numPixels += 1
            #endif
        #endfor
        rowTotals.append(numPixels)
    #endfor

    # Now look for a group of 5 rows with a lot of black pixels in them
    bestSum = 0
    bestRow = -1
    for row in range(4, high):
        sum = rowTotals[row - 4] + rowTotals[row - 3] + rowTotals[row - 2] + \
            rowTotals[row - 1] + rowTotals[row]
        if sum >= bestSum:
            bestSum = sum
            bestRow = row
        #endif
    #endfor

    if bestSum >= width:
        # There is enough white space underneath the line
        return bestRow + 5
    else:
        print "Warning: cannot find header line in", inputImage
        return 0
    #endif
#endDef

def isBlack(pixel):
    return pixel[0][0] + pixel[1][0] + pixel[2][0] < 128 * 3
#enddef

def extractZone(left, top, width, height, srcFile, destFile):
    command = 'convert -crop %dx%d+%d+%d %s %s' % \
        (width, height, left, top, srcFile, destFile)
    os.system(command)
#enddef

if __name__ == "__main__":
    main()
#endif
