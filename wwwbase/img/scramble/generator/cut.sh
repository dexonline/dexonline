#!/bin/bash

alphabet=(a ă â b c d e f g h i î j k l m n o p q r s ș t ț u v w x y z)

for i in ${!alphabet[@]}
do
    letter=${alphabet[$i]}
    height=$((i*75))
    convert -crop 55x75+0+$height letters.png $letter.png
    optipng $letter.png
done
