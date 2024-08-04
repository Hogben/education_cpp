#include <iostream>
#include <sstream>
#include <fstream>
#include <iomanip>
#include <chrono>
#include <vector>
#include <algorithm>
#include <thread>
#include <stdlib.h>
#include <cmath>

using namespace std;

//----------------------- найти и исравить ошибку в спепени<=>умножении
static const bool USE_LOG = false;
int randint(int min_value, int max_value)
{
    int rez = chrono::system_clock::now().time_since_epoch().count() % (max_value - min_value + 1);
    return rez + min_value;
}

/*/
Л. Кэрролл в своем дневнике писал, что он тщетно трудился, 
пытаясь найти хотя бы три прямоугольных треугольника равной площади, 
у которых длины сторон были бы выражены натуральными числами. 
Составьте программу для решения этой задачи, если известно, что такие треугольники существуют. 
Напишите программу, которая находит все прямоугольные треугольники (длины стороны выражаются натуральными числами), 
площадь которых не превышает данного числа S.
/*/

/*/
struct triangle 
{
    int a;
    int b;
    int c;
    int S;
};

int main()
{
    bool run = true;
    cout << "enter 0 to exit" << endl;
    int S;

    vector<triangle> v_t;
    vector<triangle> temp_v;

    int a = 4; 
    int  b = 3;

    while (run)
    {
        v_t.clear();
        cout << "Enter values: " << endl;
        cin >> S;https://prod.liveshare.vsengsaas.visualstudio.com/join?E8387911EC0B7C2844A2BA4F4FBADE7A59BF

        if (S == 0 )   break;

        for (int a = 1; a <= S; a++)
        {
            for (int b = a; ; b++)
            {
                if (a*b > 2*S)  break;

                if (a * b <= 2 * S)
                {
                    if (sqrt(a*a + b*b) == (int )(sqrt(a*a + b*b) * 1))
                    {
                        v_t.push_back({a, b, (int)sqrt(a*a + b*b), a*b});
                    }
                }
            }

        }

        sort (v_t.begin(), v_t.end(), [](auto a, auto b) {return a.S < b.S;});

        //for (auto i : v_t) cout << i.a << ", " << i.b << ", " << i.c << ", " << i.S/2 << endl;


        int t_S = v_t.cbegin()->S;

        for (auto i : v_t)        
        {
            if (t_S == i.S)
                temp_v.push_back(i);
            else
            {
                t_S = i.S;
                if (temp_v.size() > 2)
                {
                    for (auto res : temp_v) cout << res.a << ", " << res.b << ", " << res.c << ", " << res.S/2 << endl;
                    cout << "------------------------" << endl;
                }
                temp_v.clear();
                temp_v.push_back(i);                
            }
        }
    }
    cout << "ending program..." << endl;

    return 0;
}
/*/


/*/
struct days
{
    int first_day;
    int last_day;https://prod.liveshare.vsengsaas.visualstudio.com/join?E8387911EC0B7C2844A2BA4F4FBADE7A59BF
};


int main()
{
    int guest;
    bool run = true;
    int **mas; 
    vector<days> _guests;
    while (run)
    {
        _guests.clear();
        cin >> guest;   
        if (guest == 0) break;
        for (int i = 0; i < guest; i++)
        {
            int t_int = randint(1, 100);
            _guests.push_back({t_int, randint(t_int, 100)});
        }
        sort( _guests.begin(), _guests.end(), [](auto a, auto b){return a.last_day > b.last_day;});
        mas = new int *[guest];
        for (int i = 0;i < guest; i++) 
        {
            mas[i] = new int [_guests.begin()->last_day];
            for (int k = 0; k < _guests.begin()->last_day; k++)
            {
                if (k < _guests[i].first_day - 1 || k > _guests[i].last_day - 1)
                    mas[i][k] = 0;
                else
                    mas[i][k] = 1;
            }https://prod.liveshare.vsengsaas.visualstudio.com/join?E8387911EC0B7C2844A2BA4F4FBADE7A59BF
        }
        int t_max = 1;
        int t_sum = 0;
        for (int i = 0; i < _guests.begin()->last_day; i++)
        {
            t_sum = 0;
            for (int j = 0; j < guest; j++) t_sum += mas[j][i];
            if (t_sum > t_max) t_max = t_sum;
        }
        for (auto i : _guests)
            cout << i.first_day << " ==> " << i.last_day << endl;
        cout << "==================" << endl << t_max << endl;
    }

    return 0;
}
/*/


/*/
vector<uint> del; 

void find_del(uint arg)
{
    del.clear();
    del.push_back(arg);
    del.push_back(1);
    for (int i = 2; i <= arg/2; i++)
    {https://prod.liveshare.vsengsaas.visualstudio.com/join?E8387911EC0B7C2844A2BA4F4FBADE7A59BF
        if (arg % i == 0) del.push_back(i);
    }
}

bool check_num(uint arg1, uint arg2)
{
    if ( arg1 == arg2) return false;
    bool res = true;
    int  min = (arg1 < arg2) ? arg1 : arg2;
    for (int i = 2; i <= min/2; i++)
    {Совершенным числом называется число, равное сумме своих делителей, меньших его самого. Например, 28=1+2+4+7+14. Определите, является ли данное натуральное число совершенным. Найдите все совершенные числа на данном отрезке
        if (arg1 % i  == 0 && arg2 % i == 0)
            return false;
    }
    if (arg1 % arg2 == 0 || arg2 % arg1 == 0)
        return false;
    return res;
}

bool check_triple(uint arg1, uint arg2, uint arg3)
{
    return (check_num(arg1, arg2) || check_num(arg1, arg3) || check_num(arg3, arg2));
}

int main ()
{
    uint number;
    uint number2;
    uint number3;
    while (true)
    {
        cout << "number 1: " << endl;
        cin >> number;
        cout << "number 2: " << endl;
        cin >> number2;
        cout << "number 3: " << endl;
        cin >> number3;
        if (number == 0 || number2 == 0 )    break;
        if (check_triple(number, number2, number3))
            cout << "Great job" << endl;
        else
            cout << ".....wrong" << endl;
    }
    return 0;
}

bool check_prime(int arg)
{
    bool res = true;
    for (int i = 2; i <= arg/2; i++)
    {
        if (arg % i == 0)
            return false;
    }
    return res;
}
/*/

class NiceInteger
{
    public:
        NiceInteger(int arg) 
        { 
            value = arg; 
            fill_div();
            fill_digit();
        }
        bool is_perfect();
        void fill_div();
        void fill_digit();
        bool is_prime() {return (div.size() == 2);}
        bool is_even() {return !(value & 1);}
        bool palindrome();
        bool is_symmetric();
        bool is_automorph(int arg = 2);
        int  value_by_base(int);
        int  root();
        bool happy_ticket();
        bool is_cube();
        vector<int> div;
        vector<int> digit;
        bool is_not_repeat();
        int value;
        int make_int(vector<int> &);
};

int NiceInteger::make_int(vector<int> &arg)
{
    int res = 0;
    int multi = 1;
    for (int i : arg)
    {
        res += i * multi;
        multi *= 10;
    }
    return res;
}

bool NiceInteger::is_not_repeat()
{
    int _digit[10] = {0, 0, 0, 0, 0, 0, 0, 0, 0, 0};
    for (int i : digit)
    {
        _digit[i]++;
    }
    for (int i = 0; i < 10; i++)
    {
        if (_digit[i] > 1)
            return false;
    }
    return true;
}

int  NiceInteger::root()
{
    NiceInteger *t_int;
    int res = 0;
    for (int i : digit) res += i;
    while (res > 9)
    {
        t_int = new NiceInteger(res);
        res = 0;
        for (int i : t_int->digit) res += i;
        delete t_int;
    }
    return res;
}

int  NiceInteger::value_by_base(int arg)
{
    int s = 1;
    int t_int = value;
    int res = 0;
    if (arg > 10 || arg < 2)   return -1;
    if (arg == 10)  return value;
    while (t_int > 0)
    {
        res += (t_int % arg) * s;
        s *= 10;
        t_int /= arg;
    }
    return res;
}

void NiceInteger::fill_div()
{
    div.push_back(1);
    for (int i = 2; i <= value/2; i++)
        if (value % i == 0)
            div.push_back(i);
    if (value || 0) div.push_back(value);
}

void NiceInteger::fill_digit()
{
    int t_int = value;
    if (value == 0)
    {
        digit.push_back(0);
        return;
    }
    while (t_int > 0)
    {
        digit.push_back(t_int % 10);
        t_int /= 10;
    }
}

bool NiceInteger::happy_ticket()
{
    if (digit.size() & 1) return false;
    int sum_1 = 0;
    int sum_2 = 0;
    for (int i  = 0; i < digit.size()/2; i++)
    {
        sum_1 += digit[i];
        sum_2 += digit[digit.size()/2 + i];
    }
    return (sum_1 == sum_2);
}

bool NiceInteger::is_automorph(int arg)
{
    int s = value;
    for (int i = 1; i < arg; i++)
    {
        s *= value;
    }

    NiceInteger *t_int = new  NiceInteger(s);

    for (int i = 0; i < digit.size(); i++)
    {
        if (digit[digit.size() - 1 - i] != t_int->digit[digit.size() - 1 - i])
        {
            delete t_int;
            return false;
        }
    }
    delete t_int;
    return true;
}

bool NiceInteger::is_perfect()
{
    uint sum = 1;
    for (int i = 0; i < div.size()-1;i++)
    {
        if (value % div[i] == 0) 
            sum += div[i];
    }
    return (sum == value);
}

bool NiceInteger::is_symmetric()
{
    if (digit.size() & 1) return false;
    for (int i  = 0; i < digit.size()/2; i++)
    {
        if (digit[i] != digit[digit.size()/2 + i])
            return false;
    }
    return true;
}

bool NiceInteger::palindrome()
{
    for (int i = 0; i < digit.size(); i++)
    {
        if (digit[i] != digit[digit.size()-i-1])    return false;
    }
    return true;
}

// 456 4 + 5 + 6 = 15 1 + 5 = 6 

bool NiceInteger::is_cube()
{
    int sum = 0;
    int s = value;
    for (int i = 0; i < digit.size(); i++)
    {
        sum += digit[i] * digit[i] * digit[i];
    }
    if (sum == s) return true;
    return false;
}

int _digit[10] = {0, 0, 0, 0, 0, 0, 0, 0, 0, 0};

void clear_digit()
{
    for (int i = 0; i < 10; i++)
        _digit[i] = 0;
}

void calc_book_number(int arg)
{
    int t_int = arg;
    NiceInteger *j;
    clear_digit();
    for (int i = 1; i <= arg; i++)
    {
        j = new NiceInteger(i);
        for (int k : j->digit)
        {
            _digit[k]++;
        }
        delete j;
    }
}

void full_square()
{
    NiceInteger *j;

    int x = 13;

    while (x*x <= 98765432)
    {
        j = new NiceInteger(x*x);
        if (j->is_not_repeat())
            cout << j->value << endl;
        x++;
    }  
}

void print_digit()
{
    for (int i = 0; i < 10; i++)
    {
        cout << i << "  ==> " << _digit[i] << endl;   
    }
}

int _fact[10] = {1, 1, 2, 6, 24, 120, 720, 5040, 40320, 362880};


//Преобразовать простую дробь в десятичную. Если дробь окажется периодической, то период указать в скобках. 
//Период искать в первых 100 цифрах
void decimal()
{
    vector<int> p;
    int denominator;
    int numerator;
    int remain;
    cout << "numerator: ";
    cin >> numerator; 
    cout << endl <<"denomunator: ";
    cin >> denominator;
    cout << numerator/denominator << ",";
    for (int i = 0; i < 100; i++)
    {
        remain = numerator % denominator;
        if (remain == 0) break;
        remain *=10;
        while (remain < denominator)      
        {
            cout << "0";
            p.push_back(0);
            remain *=10;
        }        
        numerator = remain; 
        cout << numerator/denominator;
    }
    cout << endl;
}

void is_interesting(int arg)
{
    vector<int> t_v;
    NiceInteger * n;
    int multi = 10;
    int t_int;
    bool run = true;
    while (run)
    {
        t_int = arg * multi + 1;
        
        for (int j = t_int; j < t_int + multi; j++)   
        {
            t_v.clear();
            n = new NiceInteger(j);
            t_v.push_back(arg);
            for (int i = 0; i < n->digit.size() - 1; i++) 
            {
                t_v.push_back(n->digit[i]);
            }       
            cout << j << " " << n->make_int(t_v) << endl;
            if (n->make_int(t_v) * arg == j)
            {
                run = false;
                cout << j << endl;
                break;
            }
        }
        multi *= 10;
        delete n;
    }
}

int main ()
{
    decimal();
}
