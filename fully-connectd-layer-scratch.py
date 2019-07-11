
# coding: utf-8

# In[1]:


'''
My test accuracy is 0.9671.

First I set several initial states to model. They are {'W1', 'b1', 'C', 'b2'} and 'dh'. 
'dh' is the dimension of hidden layer and I set it as 40, which is sensible considering about
sample size and input and output states.

Second, I construct some function will be used. 
I use sigmoid function as the non-linear function of hidden layer, 
since it is easier to calculate the derivative.

Next, I apply the SGD algorithm and use backpropogation to update hyper-parameters. 
I also change the learning rate from 0.1 to 0.001 according to different epochs.

Finally, I get a test accuray of 0.9683.
'''


# In[2]:


import numpy as np
import h5py
import time
import copy
from random import randint
#load MNIST data
MNIST_data = h5py.File('MNISTdata.hdf5', 'r')
x_train = np.float32(MNIST_data['x_train'][:] )
y_train = np.int32(np.array(MNIST_data['y_train'][:,0]))
x_test = np.float32( MNIST_data['x_test'][:] )
y_test = np.int32( np.array( MNIST_data['y_test'][:,0] ) )
MNIST_data.close()


# In[3]:


#number of inputs
num_inputs = 28*28 #d
#number of outputs
num_outputs = 10#k
#number of neurals in hidden layer
dh = 40   ##20,10
model = {}
model['W1'] = np.random.randn(dh,num_inputs) / np.sqrt(num_inputs)#d*dh
model['b1'] = np.random.randn(dh)#dh*1
model['C'] = np.random.randn(num_outputs,dh) / np.sqrt(dh) #dh*k
model['b2'] = np.random.randn(num_outputs)#k
model_grads = copy.deepcopy(model)


# In[4]:


def sigmoid(z):
    return np.exp(z)/(np.exp(z)+1)
def softmax(U):
    return (np.exp(U) / np.sum(np.exp(U)))
def dsoftmax(H):
    return np.multiply(H,(1-H))
def forward(x, y, model):
    #x is d*1 vector; y is 
    W1 = model['W1'].T
    b1 = model['b1']
    C = model['C'].T
    b2 = model['b2']
    Z = np.dot(x, W1) + b1#dh*1
    H = []#dh*1
    for i in range(len(Z)):
        H.append(sigmoid(Z[i]))
    H = np.asarray(H)
    U = np.dot(H, C) + b2
    f = softmax(U)
    return f, Z, H, U


# In[5]:


epochs = 5
#M = 100 #batch_size
n = len(x_train)
#num_mini = n//M #get the number of minibatches
alpha = 0.1
for e in range(epochs):
    #permutate data to get random data samples
    idx = np.random.choice(n, n, replace = True)
    new_x_train = x_train[idx]
    new_y_train = y_train[idx]
    #define learning rate
    if (e < 2*epochs/5 and e > epochs/5):
        alpha = 0.1
    if (e > 3*epochs/5 and e > 2*epochs/5):
        alpha = 0.01
    if (e > 4*epochs/5 and e > 3*epochs/5):
        alpha = 0.001
    for i in range(n):
        eY = np.eye(10)[new_y_train[i]]
        f, Z, H, U = forward(new_x_train[i], new_y_train[i], model)
        rho_U = -(eY-f) #k*1
        rho_b2 = rho_U
        rho_C = np.dot(H.T[:,None],rho_U[:,None].T)
        delta = np.dot(model['C'].T, rho_U)
        rho_b1 = np.multiply(delta, dsoftmax(H))
        #print(rho_b1.shape)
        rho_W = np.dot(new_x_train[i].T[:,None], rho_b1[:,None].T)
        model['C'] = model['C'] - alpha * rho_C.T
        model['b2'] = model['b2'] - alpha * rho_U
        model['b1'] = model['b1'] - alpha * rho_b1
        model['W1'] = model['W1'] - alpha * rho_W.T





total_correct = 0
for n in range( len(x_test)):
    y = y_test[n]
    x = x_test[n][:]
    p = forward(x, y, model)[0]
    prediction = np.argmax(p)
    if (prediction == y):
        total_correct += 1
print(total_correct/np.float(len(x_test) ) )

