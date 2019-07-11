# TextClassifier.py
# ---------------
# Licensing Information:  You are free to use or extend this projects for
# educational purposes provided that (1) you do not distribute or publish
# solutions, (2) you retain this notice, and (3) you provide clear
# attribution to the University of Illinois at Urbana-Champaign
#
# Created by Dhruv Agarwal (dhruva2@illinois.edu) on 02/21/2019

"""
You should only modify code within this file -- the unrevised staff files will be used for all other
files and classes when code is run, so be careful to not modify anything else.
"""
from math import log
import copy
class TextClassifier(object):
    def __init__(self):
        """Implementation of Naive Bayes for multiclass classification

        :param lambda_mixture - (Extra Credit) This param controls the proportion of contribution of Bigram
        and Unigram model in the mixture model. Hard Code the value you find to be most suitable for your model
        """
        self.lambda_mixture = 1

        #self defined
        self.num_class = 0
        self.prior = []
        self.size = 0
        self.k = 1 #laplace smoothing
        self.bigram_freq = dict()
        self.unigram_freq = dict()
        self.dictlist = []
        self.dictlist_uni = []
        self.prob_uni = []
        self.prob_bi = []
        self.prob_class = []
        self.prob_result = []
        self.start= "<s>"
        self.end = "</s>"
        self.result = []
    def fit(self, train_set, train_label):
        """
        :param train_set - List of list of words corresponding with each text
            example: suppose I had two emails 'i like pie' and 'i like cake' in my training set
            Then train_set := [['i','like','pie'], ['i','like','cake']]

        :param train_labels - List of labels corresponding with train_set
            example: Suppose I had two texts, first one was class 0 and second one was class 1.
            Then train_labels := [0,1]
        """

        # TODO: Write your code here
        self.num_class = len(list(set(train_label)))
        #calculate prior probs
        self.prior = [0.0]*self.num_class
        self.prob_class = [0.0]*self.num_class
        self.size = len(train_label)
        self.dictlist = [dict() for x in range(self.num_class)]
        self.dictlist_uni = [dict() for x in range(self.num_class)]
        for i in range(self.num_class):
            for idx in range(self.size):
                self.prior[i] += (train_label[idx] == i+1)
        self.prior = [log(elem/self.size) for elem in self.prior]
        #print(self.prior)
        #calculate bigram words probs
        #first extract bigrams
        bigram_keys = []
        unigram_keys = []
        trainset = copy.deepcopy(train_set)#copy train set to insert start and end
        for i in range(self.size):
            n = len(trainset[i])
            trainset[i].insert(0,self.start)
            trainset[i].insert(n+1,self.end)
            n = len(trainset[i])
            for j in range(n-1):
                bigram_keys.append((trainset[i][j],trainset[i][j+1]))
                unigram_keys.append(trainset[i][j])
            unigram_keys.append(trainset[i][n-1])
        bigram_keys = list(set(bigram_keys))
        unigram_keys = list(set(unigram_keys))
        num_words = len(unigram_keys)-2
        #[i for i, x in enumerate(train_label) if x == 1]
        #calculate freqs
        #first get corpus from the same class
        #self.bigram_freq = dict(zip(bigram_keys,[0 for x in range(0,len(bigram_keys))]))
        
        idxs = [0] * self.num_class
        trainidx= [0]*self.num_class #a data set separate by class
        for i in range(self.num_class):
            index = [idx for idx, x in enumerate(train_label) if x == i+1]
            idxs[i] = index
            trainidx[i] = [trainset[i] for i in index]
        #print(trainidx[0])
        #print(bigram_keys[0:100])
        totalwords_class = []
        #totalwords_class_bi = []
        
        totalwords_bi = self.k*(num_words+2)*(num_words+2)
        for i in range(self.num_class):
            n = len(trainidx[i])
            totalwords = self.k*num_words
            #self.bigram_freq = dict(zip((bigram_keys, i),[0 for x in range(0,len(bigram_keys))]))
            self.bigram_freq = dict(zip(bigram_keys,[self.k for x in range(0,len(bigram_keys))]))
            self.unigram_freq = dict(zip(unigram_keys,[self.k for x in range(0,len(unigram_keys))]))
            for j in range(n):
                n1 = len(trainidx[i][j])
                for z in range(n1-1):
                    t = (trainidx[i][j][z], trainidx[i][j][z+1])
                    self.bigram_freq[t] += 1
                    self.unigram_freq[trainidx[i][j][z]] += 1
                    #if t in bigram_keys:
                    #print(t)
                    #print(self.bigram_freq[t])
                self.unigram_freq[trainidx[i][j][z+1]] += 1
                totalwords += n1-2
                #totalwords_bi += n1
            #print(i)
            self.dictlist[i] = self.bigram_freq
            self.dictlist_uni[i] = self.unigram_freq
            totalwords_class.append(totalwords)
            #totalwords_class_bi.append(totalwords_bi)
        #print(self.dictlist[0])
        #print(self.dictlist_uni[0]["<s>"])
        #kk = self.k
        for i in range(self.num_class):
            
            #s = totalwords_class[i]
            #d2 = {k: log(v/(s)) for k, v in self.dictlist[i].items()}
            d2 = {k: log(v/(self.dictlist_uni[i][k[0]]+totalwords_bi)) for k, v in self.dictlist[i].items()}
            self.prob_bi.append(d2)

        for i in range(self.num_class):
            s = totalwords_class[i]
            self.dictlist_uni[i].pop('<s>', None)
            self.dictlist_uni[i].pop('</s>', None)
            d2 = {k: log(v/s) for k, v in self.dictlist_uni[i].items()}
            self.prob_uni.append(d2)
            #self.prob_class[i] = log10(kk/(self.dictlist_uni[i][k[0]]+totalwords_class_bi[i]))
        for i in range(self.num_class):
            for k, v in self.prob_bi[i].items():
                if k[0] == "<s>":
                    self.prob_bi[i][k] = self.prob_uni[i][k[1]]
                    #print(v)
                if k[1] == "</s>":
                    self.prob_bi[i][k] = 0
                    #print(v)
                if self.dictlist[i][k] == self.k and k[0] != "<s>" and k[1] != "</s>":
                    self.prob_class[i] = self.prob_bi[i][k]
        #a = sorted(self.prob_bi[0].items(), key=lambda x: x[1], reverse=True)
        #print(a[0:20])

        #print(self.prob_class)

        
        
        pass

    def predict(self, x_set, dev_label,lambda_mix=0.0):
        """
        :param dev_set: List of list of words corresponding with each text in dev set that we are testing on
              It follows the same format as train_set
        :param dev_label : List of class labels corresponding to each text
        :param lambda_mix : Will be supplied the value you hard code for self.lambda_mixture if you attempt extra credit

        :return:
                accuracy(float): average accuracy value for dev dataset
                result (list) : predicted class for each text
        """

        accuracy = 0.0
        result = []
        #print(dev_label[1:10])
        # TODO: Write your code here
        n = len(x_set)
        xset = copy.deepcopy(x_set)
        result1 = []
        result2 = []
        for i in range(n):
            n1 = len(xset[i])
            xkeys=[]
            output = []
            for j in range(n1):
                xkeys.append(xset[i][j])
            for z in range(self.num_class):
                s = self.prior[z]
                for zz in range(len(xkeys)):
                    if xkeys[zz] in self.prob_uni[0]:
                        s += self.prob_uni[z][xkeys[zz]]
                output.append(s)
            result1.append(output)

        for i in range(n):
            
            n1 = len(xset[i])
            xset[i].insert(0, self.start)
            xset[i].insert(n1+1, self.end)
            n1= len(xset[i])
            xkeys=[]
            for j in range(n1-1):
                k = (xset[i][j], xset[i][j+1])
                xkeys.append(k)
            xprob = dict()
            onelist = []
            for z in range(self.num_class):
                for zz in range(len(xkeys)):
                    k = xkeys[zz]
                    if k[0] in self.prob_uni[z] and k[1] in self.prob_uni[z]:
                        if k in self.prob_bi[z]:
                            xprob[k] = self.prob_bi[z][k]
                        else:
                            xprob[k] = self.prob_class[z]
                            #print(xprob[k])
                    else:
                        xprob[k] = 0
                    if k[0] == "<s>" and k[1] in self.prob_uni[z]:
                        xprob[k] = self.prob_uni[z][k[1]]
                    if k[1] == "</s>":
                        xprob[k] = 0 
                onelist.append(sum(xprob.values())+self.prior[z])
            result2.append(onelist)
        for i in range(len(result1)):
            aa = []
            for j in range(self.num_class):
                aa.append((1-self.lambda_mixture)*result1[i][j] + self.lambda_mixture*result2[i][j])
            result.append(aa)
        newr = []
        for i in range(n):
            for j in range(len(result[i])):
                if result[i][j]== max(result[i]):
                    newr.append(j+1)
                    
        #print(newr)
        #print(dev_label)
        result = newr
        acc = 0
        for i in range(n):
            if dev_label[i] == result[i]:
                acc += 1
        accuracy = acc/n
        #print(result)
        #print(dev_label)
        #print(acc)
        #print(xkeys)
        pass

        return accuracy,result

