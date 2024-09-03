#include <iostream>
#include <sstream>
#include <vector>

using namespace std;

class BigNumber
{
    public:
        BigNumber(string arg)   { init(arg); }
        BigNumber(short arg)    { sstr << arg; init(sstr.str()); }
        BigNumber(int arg)      { sstr << arg; init(sstr.str()); }  
        BigNumber(long arg)     { sstr << arg; init(sstr.str()); }  

        void init(string arg);
        void view();
        
        string sum(BigNumber *a);

        vector<short>   digit;
        string GetValue() { return value; };
    protected:
        stringstream sstr;   
        string value;
};

void BigNumber::init(string arg)
{
    value = arg;
    digit.clear();
    for (int i = arg.size(); i > 0; i--)
    {
        digit.push_back((arg[i-1]-'0'));
    }
}

void BigNumber::view()
{
    for(short i = digit.size(); i > 0; i--)
    {
        cout << digit[i-1];
    }
    cout << endl;
}

string BigNumber::sum(BigNumber *arg)
{
    vector<short>    res;
    int max, min;
    short carry = 0;
    if  (digit.size() > arg->digit.size()) 
    {
        min = arg->digit.size();
        max = digit.size();
    }
    else
    {
        max = arg->digit.size();
        min = digit.size();
    }
    int idx;
    short t_short;
    for (idx = 0; idx < min; idx++)
    {
        t_short = digit[idx] +  arg->digit[idx] + carry;
        carry = (t_short > 9) ? 1 : 0;
        res.push_back(t_short%10);
    }    
    for (idx = min; idx < max; idx++)
    {
        if (max == digit.size())
            t_short = digit[idx] + carry;
        else
            t_short = arg->digit[idx] + carry;
        carry = (t_short > 9) ? 1 : 0;
        res.push_back(t_short%10);
    }
    if (carry > 0) 
        res.push_back(1);

    sstr.str("");
    
    for(short i = res.size(); i > 0; i--)
    {
        sstr << res[i-1];
    }

    return sstr.str();
}

int main()
{
    cout << BigNumber(1).sum(new BigNumber("9999999999999999999999999999")) << endl;
}