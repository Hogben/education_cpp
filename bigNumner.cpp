#include <iostream>
#include <sstream>
#include <vector>

using namespace std;

class BigNumber
{
    public:
        BigNumber(string arg)   { init(arg); }
        BigNumber(short arg)    { s_str << arg; init(s_str.str()); }
        BigNumber(int arg)      { s_str << arg; init(s_str.str()); }  
        BigNumber(long arg)     { s_str << arg; init(s_str.str()); }  

        void init(string arg);
        void view();

        string sum(string val, BigNumber* bn);
        string sub(string val, BigNumber* bn);

        vector<short>   digit;
        string GetValue() { return value; };
        bool IsPositive() { return positive; };
    protected:
        stringstream s_str;   
        string value;
        bool positive = true;
};

void BigNumber::init(string arg)
{
    if (arg[0] == '-')  
    {
        positive = false;
        arg = arg.substr(1,arg.size()-1);
    }
    value = arg;
    digit.clear();
    for (int i = arg.size(); i > 0; i--)
    {
        digit.push_back((arg[i-1]-'0'));
    }
}

void BigNumber::view()
{
    if (!IsPositive())  cout << "-";
    for(int i = digit.size(); i > 0; i--)
    {
        cout << digit[i-1];
    }
    cout << endl;
}

string BigNumber::sum(string s_arg, BigNumber* arg)
{
    vector<short>    res;
    int max, min;
    short carry = 0;
    BigNumber *t_num = new BigNumber(s_arg);
    stringstream sstr;   

//    t_num->view();
//    arg->view();
    sstr.str("");

    if (t_num->IsPositive() && arg->IsPositive())
    {
        if  (t_num->digit.size() > arg->digit.size()) 
        {
            min = arg->digit.size();
            max = t_num->digit.size();
        }
        else
        {
            max = arg->digit.size();
            min = t_num->digit.size();
        }
        int idx;
        short t_short;
        for (idx = 0; idx < min; idx++)
        {
            t_short = t_num->digit[idx] +  arg->digit[idx] + carry;
            carry = (t_short > 9) ? 1 : 0;
            res.push_back(t_short%10);
        }    
        for (idx = min; idx < max; idx++)
        {
            if (max == t_num->digit.size())
                t_short = t_num->digit[idx] + carry;
            else
                t_short = arg->digit[idx] + carry;
            carry = (t_short > 9) ? 1 : 0;
            res.push_back(t_short%10);
        }
        if (carry > 0) 
            res.push_back(1);

        for(int i = res.size(); i > 0; i--)
        {
            sstr << res[i-1];
        }

        return sstr.str();
    }
    else
    {
        if (t_num->IsPositive() && !arg->IsPositive())
        {
            return(sub(t_num->GetValue(), new BigNumber(arg->GetValue())));
        }
        else
        {
            if (!t_num->IsPositive() && arg->IsPositive())
            {   
                return(sub(arg->GetValue(), new BigNumber(t_num->GetValue())));
            }
            else
            {
                sstr << "-";
                sstr << sum(t_num->GetValue(), new BigNumber(arg->GetValue()));
                return sstr.str();
            }
        }
    }

}

string BigNumber::sub(string a, BigNumber *b)
{
    if (!b->IsPositive()) return sum(a, b);
    return "sub";
}

int main()
{
    BigNumber *n1 = new BigNumber(-11);
    cout << BigNumber(32233232).sum("-11", new BigNumber(-11)) << endl;
}