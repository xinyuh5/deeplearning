/**
 * @file kdtree.cpp
 * Implementation of KDTree class.
 */

#include <utility>
#include <algorithm>
#include <cmath>
using namespace std;

template <int Dim>
bool KDTree<Dim>::smallerDimVal(const Point<Dim>& first,
                                const Point<Dim>& second, int curDim) const
{
    /**
     * @todo Implement this function!
     */
    //bool less = false;
    if (first[curDim] < second[curDim]){
      return true;
    }else if (first[curDim] > second[curDim]){
      return false;
    }else{
      return (first<second);
    }
    //return false;
}

template <int Dim>
bool KDTree<Dim>::shouldReplace(const Point<Dim>& target,
                                const Point<Dim>& currentBest,
                                const Point<Dim>& potential) const
{
    /**
     * @todo Implement this function!
     */
    double d_cb=0;
    double d_pt=0;
    for (int i = 0; i < Dim; ++i) {
        d_cb = d_cb + pow((target[i]-currentBest[i]), 2);
        d_pt = d_pt + pow((target[i]-potential[i]), 2);
    }
    if (d_pt < d_cb){
      return true;
    }else if (d_pt > d_cb){
     return false;
    }else{
      return (potential<currentBest);
    }
}
//helper funtion
template <int Dim>
Point<Dim> KDTree<Dim>::quickselect(vector<Point<Dim>>& newPoints, int start, int end, int k, int currdim){
  if (start == end){return newPoints[start];}
  int split;
  //int k = ((double)end-(double)start+1)/2;
  split = partition(newPoints, start, end, currdim);
  //int length;
  //length = split - start +1;
  if (split == k){
    //cout<<split<<"split"<<endl;
    //cout<<newPoints[split]<<"sssss"<<endl;
    return newPoints[split];
  }else if(k < split){
    return quickselect(newPoints, start, split-1, k, currdim);
  }else{
    return quickselect(newPoints, split+1, end, k, currdim);
  }
}
template <int Dim>
int KDTree<Dim>::partition(vector<Point<Dim>>& newPoints, int start, int end, int currdim){
  Point<Dim> pivot = newPoints[start];
  Point<Dim> temp;
  int startMark = start+1;
  int endMark = end;
  while (true){
    while (startMark < end && smallerDimVal(newPoints[startMark], pivot, currdim)){
      startMark = startMark+1;
    }
    while (endMark > start && smallerDimVal(pivot, newPoints[endMark], currdim)){
      endMark = endMark-1;
    }
    if (startMark >= endMark){
      break;
    }else{
      temp = newPoints[startMark];
      newPoints[startMark] = newPoints[endMark];
      newPoints[endMark] = temp;
    }
  }
  temp = newPoints[start];
  newPoints[start] = newPoints[endMark];
  newPoints[endMark] = temp;
  return endMark;
}
template <int Dim>
typename KDTree<Dim>::KDTreeNode* KDTree<Dim>::build(vector<Point<Dim>>& newPoints, int start, int end, int currdim){
  KDTreeNode* subroot;
  //cout<<start<<"start"<<end<<"end"<<endl;
  if (start>end){return NULL;}
  /*double kk;
  kk = ((double)end-(double)start+1)/2;
  int k = ceil(kk);*/
  /*if (k<=1){
    subroot = new KDTreeNode(newPoints[start]);
    cout<<subroot->point<<"s"<<endl;
    return subroot;}*/
  int k = (end+start)/2;
  //cout<<k<<"k"<<endl;
  Point<Dim> p = quickselect(newPoints, start, end, k, currdim);
  //cout<<p<<"p"<<endl;
  currdim = (currdim+1)%Dim;
  subroot = new KDTreeNode(p);
  //cout<<subroot->point<<"s"<<endl;
  //if (start>=end){return subroot;}
  subroot->left = build(newPoints, start, k-1, currdim);
  //start = start+k-1;
  //cout<<subroot->point<<"s"<<endl;
  subroot->right = build(newPoints, k+1, end, currdim);
  //cout<<subroot->point<<"s"<<endl;
  return subroot;
}
template <int Dim>
KDTree<Dim>::KDTree(const vector<Point<Dim>>& newPoints)
{
    /**
     * @todo Implement this function!
     */
    if (newPoints.empty()){root = new KDTreeNode();}
    Points = newPoints;
    size_t size = newPoints.size();
    
    //int start = 0;
    //int k = floor((size+start)/2);
    
    root = build(Points, 0, size-1, 0);
    //cout<<root->point<<"root"<<endl;

}


/*template <int Dim>
void KDTree<Dim>::clear(KDTreeNode*& subroot){
  if(subroot!=NULL){
    clear(subroot->left);
    clear(subroot->right);
    delete subroot;
  }
}*/
template <int Dim>
void KDTree<Dim>::_delete(KDTreeNode* subroot){
  if(subroot == NULL){
    //cout<<"null"<<endl;
    return;
  }else{
  //cout<<subroot<<endl;
  _delete(subroot->left);
  _delete(subroot->right);
  delete subroot;
  subroot = NULL;}
}
template <int Dim>
void KDTree<Dim>::_copy(KDTreeNode* curr_node){
  if(curr_node == NULL){
    return;
  }
  KDTreeNode* new_node = new KDTreeNode(curr_node->point);
  new_node->left = _copy(curr_node->left);
  new_node->right = _copy(curr_node->right);
  return new_node;
}


template <int Dim>
KDTree<Dim>::KDTree(const KDTree<Dim>& other) {
  /**
   * @todo Implement this function!
   */
  //this->Points = other.Points;
    _copy(this->root, other.root);
}

template <int Dim>
const KDTree<Dim>& KDTree<Dim>::operator=(const KDTree<Dim>& rhs) {
  /**
   * @todo Implement this function!
   */
  if (this!=&rhs){
  _delete(this->root);
  _copy(this->root, rhs.root);}
  return *this;
}

template <int Dim>
KDTree<Dim>::~KDTree() {
  /**
   * @todo Implement this function!
   */
  //clear(root);
  _delete(this->root);
}

template <int Dim>
Point<Dim> KDTree<Dim>::findNearestNeighbor(const Point<Dim>& query) const
{
    /**
     * @todo Implement this function!
     */
    if (Points.empty()){
        return NULL;
    }
    return Points[findHelper(query, 0, 0, Points.size()-1)];
    //return Point<Dim>();
}

template <int Dim>
double KDTree<Dim>::getdis(const Point<Dim>& first, const Point<Dim>& second) const
{
  double dis = 0;
  for (int i = 0; i < Dim; i++) {
    dis += pow((first[i] - second[i]), 2);
  }
  return dis;
}

template <int Dim>
int KDTree<Dim>::findHelper(const Point<Dim>& query, int currdim, int start, int end) const
{
    /**
     * @todo Implement this function!
     */
  int bestidx, nextbest; 
  if (start >= end){return start;}
  int middle = (start+end)/2;
  if (smallerDimVal(query, Points[middle], currdim)){
    bestidx = findHelper(query, (currdim+1)%Dim, start, middle-1);
  }else{
    bestidx = findHelper(query, (currdim+1)%Dim, middle+1, end);
  }
  if (shouldReplace(query, Points[bestidx], Points[middle])){
    bestidx = middle;
  }
      
  double bestdis = getdis(query, Points[bestidx]);
  double dis2 = (query[currdim]-Points[middle][currdim])*(query[currdim]-Points[middle][currdim]);
  //search another tree;
  if(bestdis>=dis2){ 
    if(smallerDimVal(query,Points[middle],currdim)) 
      nextbest = findHelper(query, (currdim+1)%Dim, middle+1,end);
    else{
      nextbest = findHelper(query, (currdim+1)%Dim, start,middle-1);
    }
    if (shouldReplace(query, Points[bestidx], Points[nextbest]))
        bestidx = nextbest;
  }
  return bestidx;
    //return Point<Dim>();
}