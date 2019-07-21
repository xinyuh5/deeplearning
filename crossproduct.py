#!/bin/python3

import math
import os
import random
import re
import sys

# Complete the crosswordPuzzle function below.
def dictconstruct(crossword):
    h = dict()
    v = dict()
    for i in range(10):
        temp = []
        for j in range(10):
            if crossword[i][j] == "-":
                temp.append(j)
        if len(temp) > 1:
            tempc = temp.copy()
            tempc.pop(0)
            tempc.append(temp[-1]+2)
            check = dict(zip(temp, tempc))
            t = []
            for key, item in check.items():
                if item - key == 1:
                    t.append(key)
            
            
            if len(t) > 1:
                #print(t)
                t.append(t[-1]+1)
                h[i] = t
    for m in range(10):
        temp = []
        for n in range(10):
            if crossword[n][m] == "-":
                temp.append(n)
        #print(temp)
        if len(temp) > 1:
            tempc = temp.copy()
            tempc.pop(0)
            tempc.append(temp[-1]+2)
            #print(tempc)
            check = dict(zip(temp, tempc))
            t = []
            for key, item in check.items():
                if item - key == 1:
                    t.append(key)
            
            if len(t) > 1:
                t.append(t[-1]+1)
                v[m] = t
            
    return h, v
def solveh(crossword, word, h):
    print(word)
    #print(crossword, 'h')
    #print(len(word))
    copycross = crossword.copy()
    for key, items in h.items():
        if len(word) == len(items):
            for i in items:
                crossword[key] = list(crossword[key])
                if crossword[key][i] != '-' and crossword[key][i] != word[0]:
                    crossword = copycross
                    break
                 
                crossword[key][i] = word[0]
                crossword[key] = ''.join(crossword[key])
                word.pop(0)
            else: 
                if word == []:
                    print(crossword)
                    return crossword
    return False
def solvev(crossword, word, v):
    #print(crossword, 'v')
    copycross = crossword.copy()
    for key, items in v.items():
        print(key, items)
        print(word)
        if len(word) == len(items):
            for i in items:
                crossword[i] = list(crossword[i])
                print(78,crossword[i],word[0])
                if crossword[i][key] != '-' and crossword[i][key] != word[0]:
                    crossword = copycross
                    break
                crossword[i][key] = word[0]
                
                crossword[i] = ''.join(crossword[i])
                print(85,crossword[i])
                word.pop(0)
            else: 
                if word == []:
                    print(87,crossword)
                    return crossword
    return False
def issolved(crossword):
    #print(999, crossword)
    for i in range(10):
        for j in range(10):
            if crossword[i][j]=='-':
                return False
    return True
def helper(crossword, stripwords, words, h, v, original, count):
    #print(crossword, 88)
    #print(words, 89)
    print(stripwords, 90)
    if issolved(crossword):
        return crossword
    stripwords = stripwords.split(';')
    pwords = words.split(';')
    word = list(stripwords[0])
    
    #print(word, 93)
    copycross = crossword.copy()
    copywords = pwords.copy()
    while stripwords!=[]:
        crossword = solveh(crossword, word, h)
        #print(100,stripwords, crossword)
        if crossword == False:
            crossword = solvev(copycross, word, v)
            if crossword == False:
                count += 1
                copywords.append(copywords[0])
                
                copywords.pop(0)
                #print(count,len(words.split(';')), words,'hhhhhhhh')
                if count == len(words.split(';')):
                    count = 0 # Copy words
                    w = random.sample(copywords, len(copywords))
                    #print(1111,w)
                    return(helper(original.copy(), ';'.join(w), ';'.join(w), h, v, original, count))
                return(helper(original.copy(), ';'.join(copywords), ';'.join(copywords), h, v, original, count))
            else: 
                print(119,stripwords, crossword)
                stripwords.pop(0)
                print(121, stripwords)
                if stripwords != []:
                    word = list(stripwords[0])
        else:
            stripwords.pop(0)
            #print(126, stripwords)
            if stripwords != []:
                word = list(stripwords[0])
            copycross = crossword.copy()
            #return(helper(crossword, ';'.join(stripwords), words, h, v, original))
            #print(114, crossword)
    return crossword
def crosswordPuzzle(crossword, words):
    h, v = dictconstruct(crossword)
    print(h, v)
    stripwords = words
    original = crossword.copy()
    #print(135,original)
    crossword = helper(crossword, stripwords, words, h, v, original, count=0)
    #print('136',crossword)
    return crossword

if __name__ == '__main__':
    fptr = open(os.environ['OUTPUT_PATH'], 'w')

    crossword = []

    for _ in range(10):
        crossword_item = input()
        crossword.append(crossword_item)

    words = input()

    result = crosswordPuzzle(crossword, words)

    fptr.write('\n'.join(result))
    fptr.write('\n')

    fptr.close()