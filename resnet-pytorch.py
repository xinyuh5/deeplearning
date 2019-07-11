import torch
import torch.nn as nn
import torch.nn.functional as F
import torch.optim as optim
from torchvision import datasets, transforms
from torch.autograd import Variable
import torchvision
import time
import torch.backends.cudnn as cudnn

import math
import os
import numpy as np


torch.cuda.is_available()
device = torch.device('cuda:0' if torch.cuda.is_available() else 'cpu')

# Hyper-parameters
num_epochs = 100
learning_rate = 0.01#/10.0
batch_size = 128
DIM = 32
no_of_hidden_units = 196
num_classes = 100



transform_train = transforms.Compose([
    transforms.RandomResizedCrop(DIM, scale=(0.7, 1.0), ratio=(1.0,1.0)),
    transforms.ColorJitter(
            brightness=0.1*torch.randn(1),
            contrast=0.1*torch.randn(1),
            saturation=0.1*torch.randn(1),
            hue=0.1*torch.randn(1)),
    transforms.RandomHorizontalFlip(),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225]),
])

transform_test = transforms.Compose([
    transforms.Resize(DIM, interpolation=2),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225]),
])

trainset = torchvision.datasets.CIFAR100(root='./', train=True, download=True, transform=transform_train)
trainloader = torch.utils.data.DataLoader(trainset, batch_size=batch_size, shuffle=True, num_workers=8)

testset = torchvision.datasets.CIFAR100(root='./', train=False, download=False, transform=transform_test)
testloader = torch.utils.data.DataLoader(testset, batch_size=batch_size, shuffle=False, num_workers=8)
print('load data succeed')
# Construct model
# Convolutional neural network (two convolutional layers)
class BasicBlock(nn.Module):
    expansion = 1

    def __init__(self, in_planes, planes, stride=1):
        super(BasicBlock, self).__init__()
        self.conv1 = nn.Conv2d(in_planes, planes, kernel_size=3, stride=stride, padding=1, bias=False)
        self.bn1 = nn.BatchNorm2d(planes)
        self.conv2 = nn.Conv2d(planes, planes, kernel_size=3, stride=1, padding=1, bias=False)
        self.bn2 = nn.BatchNorm2d(planes)

        self.shortcut = nn.Sequential()
        if stride != 1 or in_planes != self.expansion*planes:
            self.shortcut = nn.Sequential(
                nn.Conv2d(in_planes, self.expansion*planes, kernel_size=1, stride=stride, bias=False),
                nn.BatchNorm2d(self.expansion*planes)
            )

    def forward(self, x):
        out = F.relu(self.bn1(self.conv1(x)))
        out = self.bn2(self.conv2(out))
        out += self.shortcut(x)
        out = F.relu(out)
        return out


class ResNet(nn.Module):
    def __init__(self, block, num_blocks, num_classes=100):
        super(ResNet, self).__init__()
        self.in_planes = 32

        self.conv1 = nn.Conv2d(3, 32, kernel_size=3, stride=1, padding=1, bias=False)
        self.bn1 = nn.BatchNorm2d(32)
        self.dropout1 = nn.Dropout2d(p=0.2)
        self.layer1 = self._make_layer(block, 32, num_blocks[0], stride=1)
        self.layer2 = self._make_layer(block, 64, num_blocks[1], stride=2)
        self.layer3 = self._make_layer(block, 128, num_blocks[2], stride=2)
        self.layer4 = self._make_layer(block, 256, num_blocks[3], stride=2)
        self.linear = nn.Linear(256*block.expansion, num_classes)

    def _make_layer(self, block, planes, num_blocks, stride):
        strides = [stride] + [1]*(num_blocks-1)
        layers = []
        for stride in strides:
            layers.append(block(self.in_planes, planes, stride))
            self.in_planes = planes * block.expansion
        return nn.Sequential(*layers)

    def forward(self, x):
        out = F.relu(self.bn1(self.conv1(x)))
        out = self.dropout1(out)
        out = self.layer1(out)
        out = self.layer2(out)
        out = self.layer3(out)
        out = self.layer4(out)
        out = F.max_pool2d(out, 4)
        out = out.view(out.size(0), -1)
        out = self.linear(out)
        return out



def ResNet_self24():
    return ResNet(BasicBlock, [2,4,4,2])

def test():
    net = ResNet_self24()
    y = net(torch.randn(1,3,32,32))
    print(y.size())

test()



model = ResNet_self24().to(device)
#if device == 'cuda':
  #  model = torch.nn.DataParallel(model)
  #  cudnn.benchmark = True
# Loss and optimizer
criterion = nn.CrossEntropyLoss()
optimizer = optim.SGD(model.parameters(), lr=learning_rate, momentum=0.9, weight_decay=1e-4)
#scheduler = torch.optim.lr_scheduler.StepLR(optimizer, step_size=60, gamma=0.1)

start_time = time.time()
print("start training")



# Train the model
for epoch in range(0,num_epochs):

    test_accu = []
    #scheduler.step()
    #state = optimizer.state_dict()['state']
    for batch_idx, (X_train_batch, Y_train_batch) in enumerate(trainloader):

        if(Y_train_batch.shape[0]<batch_size):
            continue

        #X_train_batch = Variable(X_train_batch).cuda()
        #Y_train_batch = Variable(Y_train_batch).cuda()
        #data, target = X_train_batch.to(device), Y_train_batch.to(device)
        #data, target = Variable(X_train_batch).cuda(), Variable(Y_train_batch).cuda()
        X_train_batch = Variable(X_train_batch).cuda()
        Y_train_batch = Variable(Y_train_batch).cuda()
        data, target = Variable(X_train_batch), Variable(Y_train_batch)
        optimizer.zero_grad()
        h = model(data)
        loss = criterion(h,target)
        #pred = F.softmax(h,dim=1)
        
        loss.backward()
        optimizer.step()   # update gradients
        
        '''prediction = pred.data.max(1)[1]   # first column has actual prob.
        epoch_acc += float(prediction.eq(target.data).sum())
        epoch_loss += loss.item() # loss.data[0]
        epoch_counter += batch_size
    epoch_acc /= epoch_counter
    epoch_loss /= (epoch_counter/batch_size)

    train_loss.append(epoch_loss)
    train_accu.append(epoch_acc)

    print(epoch, "%.2f" % (epoch_acc*100.0), "%.4f" % epoch_loss)'''

    ## test
    model.eval()

    epoch_acc = 0.0
    epoch_loss = 0.0

    epoch_counter = 0

    #time1 = time.time()


    for batch_idx, (x_batch, y_batch) in enumerate(testloader):

       
        if(y_batch.shape[0]<batch_size):
            continue
        #target = y_batch.cuda()
        #x_batch = Variable(x_batch).cuda()
        #y_batch = Variable(y_batch).cuda()
        #data, target = x_batch.to(device), y_batch.to(device)
        #data, target = Variable(x_batch).cuda(), Variable(y_batch).cuda()
        x_batch = Variable(x_batch).cuda()
        y_batch = Variable(y_batch).cuda()
        data, target = Variable(x_batch), Variable(y_batch)

        h = model(data)
        #h = Variable(h)
        loss = criterion(h,target)
        pred = F.softmax(h,dim=1)
        
        prediction = pred.data.max(1)[1]   # first column has actual prob.
        epoch_acc += float(prediction.eq(target.data).sum())
        epoch_loss += loss.item() # loss.data[0]
        epoch_counter += batch_size

    epoch_acc /= epoch_counter
    epoch_loss /= (epoch_counter/batch_size)

    test_accu.append(epoch_acc)

    time2 = time.time()
    time_elapsed = time2 - start_time

    print("TEST:  ", "%.2f" % (epoch_acc*100.0), "%.4f" % epoch_loss)


#########################
####pretrained part2######
##########################

# Hyper-parameters
num_epochs = 100
learning_rate = 0.001#/10.0
batch_size = 64
DIM = 224
no_of_hidden_units = 196
num_classes = 100



torch.cuda.is_available()
device = torch.device('cuda:0' if torch.cuda.is_available() else 'cpu')
#DIM = 224

transform_train = transforms.Compose([
    transforms.RandomResizedCrop(DIM, scale=(0.7, 1.0), ratio=(1.0,1.0)),
    transforms.ColorJitter(
            brightness=0.1*torch.randn(1),
            contrast=0.1*torch.randn(1),
            saturation=0.1*torch.randn(1),
            hue=0.1*torch.randn(1)),
    transforms.RandomHorizontalFlip(),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225]),
])

transform_test = transforms.Compose([
    transforms.Resize(DIM, interpolation=2),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225]),
])
trainset = torchvision.datasets.CIFAR100(root='./', train=True, download=True, transform=transform_train)
trainloader = torch.utils.data.DataLoader(trainset, batch_size=batch_size, shuffle=True, num_workers=8)

testset = torchvision.datasets.CIFAR100(root='./', train=False, download=False, transform=transform_test)
testloader = torch.utils.data.DataLoader(testset, batch_size=batch_size, shuffle=False, num_workers=8)
print('load data succeed')

import torchvision.models as models

model = models.resnet18(pretrained=True)#.to(device)
model.fc = nn.Linear(512,100)
model.to(device)
print("model load succeed")
start_time = time.time()
criterion = nn.CrossEntropyLoss()
optimizer = torch.optim.SGD(list(model.layer4.parameters())+list(model.fc.parameters()), lr = 0.01, momentum=0.9)
scheduler = torch.optim.lr_scheduler.StepLR(optimizer, step_size=60, gamma=0.1)
# Train the model
print("start training")
for epoch in range(0,num_epochs):

    test_accu = []
    scheduler.step()
    #state = optimizer.state_dict()['state']
    for group in optimizer.param_groups:
            for p in group['params']:
                state = optimizer.state[p]
                if('step' in state and state['step']>=1000):
                    state['step'] = 1000
    for batch_idx, (X_train_batch, Y_train_batch) in enumerate(trainloader):

        if(Y_train_batch.shape[0]<batch_size):
            continue

        X_train_batch = Variable(X_train_batch).cuda()
        Y_train_batch = Variable(Y_train_batch).cuda()
        data, target = Variable(X_train_batch), Variable(Y_train_batch)
        #data, target = Variable(X_train_batch).cuda(), Variable(Y_train_batch).cuda()
        optimizer.zero_grad()
        with torch.no_grad():
            h = model.conv1(data)
            h = model.bn1(h)
            h = model.relu(h)
            h = model.maxpool(h)
            h = model.layer1(h)
            h = model.layer2(h)
            h = model.layer3(h)
        h = model.layer4(h)
        h = model.avgpool(h)
        h = h.view(h.size(0), -1)
        h = model.fc(h)
        #h = model(data)

        loss = criterion(h,target)
        pred = F.softmax(h,dim=1)
        
        loss.backward()
        optimizer.step()   # update gradients
        
        '''prediction = pred.data.max(1)[1]   # first column has actual prob.
        epoch_acc += float(prediction.eq(target.data).sum())
        epoch_loss += loss.item() # loss.data[0]
        epoch_counter += batch_size
    epoch_acc /= epoch_counter
    epoch_loss /= (epoch_counter/batch_size)

    train_loss.append(epoch_loss)
    train_accu.append(epoch_acc)

    print(epoch, "%.2f" % (epoch_acc*100.0), "%.4f" % epoch_loss)'''

    ## test
    model.eval()

    epoch_acc = 0.0
    epoch_loss = 0.0

    epoch_counter = 0

    #time1 = time.time()


    for batch_idx, (x_batch, y_batch) in enumerate(testloader):

       
        if(y_batch.shape[0]<batch_size):
            continue
        #target = y_batch.cuda()
        x_batch = Variable(x_batch).cuda()
        y_batch = Variable(y_batch).cuda()
        data, target = Variable(x_batch), Variable(y_batch)
        #data, target = Variable(x_batch).cuda(), Variable(y_batch).cuda()
        h = model(data)
        #h = Variable(h)
        loss = criterion(h,target)
        pred = F.softmax(h,dim=1)
        
        prediction = pred.data.max(1)[1]   # first column has actual prob.
        epoch_acc += float(prediction.eq(target.data).sum())
        epoch_loss += loss.item() # loss.data[0]
        epoch_counter += batch_size

    epoch_acc /= epoch_counter
    epoch_loss /= (epoch_counter/batch_size)

    test_accu.append(epoch_acc)

    time3 = time.time()
    time_elapsed = time3 - start_time

    print("TEST:  ", "%.2f" % (epoch_acc*100.0), "%.4f" % epoch_loss)

#output = model(x)
