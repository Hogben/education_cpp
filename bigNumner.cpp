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

        BigNumber& _pointer()   { return *this; }

        void init(string arg);
        void view();

        friend bool operator == (const BigNumber& a, const BigNumber& b) 
        {
           if (a.positive == b.positive && a.digit.size() == b.digit.size())
           {
                for (int i = 0; i < a.digit.size(); i++)
                {
                    if (a.digit[i] != b.digit[i])   return false;
                }       
                return true;
           }
           return false;
        }

        friend bool operator > (const BigNumber& a, const BigNumber& b) 
        {   
            if (a == b) return false;
            if (a.positive && !b.positive)  return true;
            if (!a.positive && b.positive)  return false;
            if (a.digit.size() > b.digit.size())    return a.positive;
            if (a.digit.size() < b.digit.size())    return !a.positive;
            for (int i = a.digit.size(); i > 0; i--)
            {
                if (a.digit[i-1] > b.digit[i-1])    return a.positive;
                if (a.digit[i-1] < b.digit[i-1])    return !a.positive;
            }
            return false;
        }

        friend bool operator < (const BigNumber& a, const BigNumber& b) 
        {   
            return (!(a > b));
        }

        friend ostream& operator << (ostream &ostrm, const BigNumber& a) { return ostrm << (a.positive ? "" : "-") << a.value; }

        BigNumber& add(BigNumber* bn);
        BigNumber& sub(BigNumber* bn);
        
        BigNumber& multi(BigNumber* bn);
        BigNumber& div(BigNumber* bn);

        vector<short>   digit;
        string GetValue() { return value; };
        bool IsPositive() { return positive; };
        bool positive = true;

        string remainder;
    protected:
        stringstream s_str;   
        string value;
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
    remainder = "0";
}


void BigNumber::view()
{
    if (!IsPositive())  cout << "-";
    cout << value;
    cout << endl;
}

BigNumber& BigNumber::multi(BigNumber* arg)
{
    bool final_positive = !(positive ^ arg->positive);
    BigNumber   *t_bn;
    string      main_number;

    if (value != "0" && arg->value != "0")
    {
        positive = true;
        arg->positive = true;
        if (*this > *arg)
        {
            t_bn = new BigNumber(arg->value);
            main_number = value;
        }
        else
        {
            t_bn = new BigNumber(value);
            main_number = arg->value;
        }
    
        while (true)
        {
            if (t_bn->digit.size() == 1 && t_bn->digit[0] == 1)
                break;
            add(new BigNumber(main_number));
            t_bn->sub(new BigNumber(1));
        }
        positive = final_positive;
    }
    else
        init("0");

    return *this;
}

BigNumber& BigNumber::add(BigNumber* arg)
{
    vector<short>    res;
    int max, min;
    short carry = 0;
    stringstream sstr;   

    sstr.str("");

    if (IsPositive() && arg->IsPositive())
    {
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

        for(int i = res.size(); i > 0; i--)
        {
            sstr << res[i-1];
        }

        init(sstr.str());
    }
    else
    {
        if (IsPositive() && !arg->IsPositive())
        {
            sub(new BigNumber(arg->GetValue()));
        }
        else
        {
            if (!IsPositive() && arg->IsPositive())
            {   
                arg->sub(new BigNumber(GetValue()));
            }
            else
            {
                BigNumber* n1 = new BigNumber(GetValue());
                n1->add(new BigNumber(arg->GetValue()));
                sstr << "-" << n1->GetValue();
                init (sstr.str());
            }
        }
    }
    return *this;
}

BigNumber& BigNumber::sub(BigNumber *b)
{   
    if (*this == *b)
    {
        init("0");
    }
    else
    {
        if (!b->IsPositive()) add(new BigNumber(b->GetValue()));
        else
        {
            if (!positive)
            {
                positive = true;
                add(new BigNumber(b->GetValue()));
                positive = false;   
            }
            else
            {       
                vector<short>    res;
                BigNumber *n1, *n2;
                int min, max;
                stringstream sstr;   

                bool need_negative = false;

                if (*this > *b)
                {
                    max = digit.size();
                    min = b->digit.size();
                    n1 = new BigNumber(GetValue());
                    n2 = new BigNumber(b->GetValue());
                }
                else
                {
                    need_negative = true;
                    min = digit.size();
                    max = b->digit.size();
                    n1 = new BigNumber(b->GetValue());
                    n2 = new BigNumber(GetValue());
                }
                int j;
                int i;
                for (i = 0; i < min; i++)
                {
                    if (n1->digit[i] < n2->digit[i])
                    {
                        res.push_back((n1->digit[i] +10) - n2->digit[i]);  
                        j = i + 1;
                        while (j < max)
                        {
                            if (n1->digit[j] > 0)
                            {
                                n1->digit[j]--;    
                                break;
                            }
                            else
                            {
                                n1->digit[j] = 9;
                                j++;    
                            }
                        }  
                    }
                    else
                    {
                        res.push_back(n1->digit[i] - n2->digit[i]);
                    }
                }
                for (; i < max; i++)
                    res.push_back(n1->digit[i]);

                bool print_sym = false;

                for(int i = res.size(); i > 0; i--)
                {
                    if (res[i-1] > 0)  print_sym = true;
                    if (print_sym)  sstr << res[i-1];
                }

                init(sstr.str());
                if (need_negative)  positive = false;

            }
        }
    }

    return *this;
}

int main()
{
    BigNumber *n1 = new BigNumber("999999999999999");

    cout << n1->multi(new BigNumber("-45430")) << endl;
}