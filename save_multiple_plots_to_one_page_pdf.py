#reference: https://stackoverflow.com/questions/45157243/saving-multiple-plots-on-a-single-pdf-page-using-matplotlib
import matplotlib.pyplot as plt
from matplotlib.backends.backend_pdf import PdfPages #create one figure per page
import numpy as np

with PdfPages('test.pdf') as pdf:
    t = np.arange(0.0, 2.0, 0.01) #t = 0,0.01,0.02,...,1.99,2.0
    s = 1 + np.sin(2*np.pi*t)
    s = s * 50

    fig = plt.figure(figsize=(12,12)) #create a figure
    n=0
    for i in range(11):
        n += 1
        ax = fig.add_subplot(4,3,n)   #use subplots to add plots into one figure, (nrows, ncols, indexes)
        ax.plot(t, s, linewidth=3, label='a')
        ax.plot(t, s / 2, linewidth=3, label='b')
        ax.set_ylim(0, 100)
        ax.legend()
        ax.yaxis.set_label_text('Excess movement (%)')
        plt.setp(ax.xaxis.get_ticklabels(), rotation='45')
    pdf.savefig(fig)
