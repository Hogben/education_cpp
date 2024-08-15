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
#include <map>

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
        cin >> S;

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

        //for (auto i : v_t) cout << i.a << "Написать программу для кодирования данного текста с помощью азбуки Морзе

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
    int last_day;
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
            }
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
    {
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
        string make_rome_number();
};

string NiceInteger::make_rome_number()
{
    /*/
    1 = I
    5 = V
    10 = X
    50 = L
    100 = C
    500 = D
    1000 = M
    
    9  = IX
    14 = XIV
    602 = DCII

    902 = CMII
    /*/
    stringstream t_str;

    if (digit.size() > 3)
    {
        for (int i = 0; i < value / 1000; i++)  t_str << "M";
    }
    if (digit[2] > 0)
    {
        switch (digit[2])
        {
            case 1:
                t_str << "C";
                break;
            case 2:
                t_str << "CC";
                break;
            case 3:
                t_str << "CCC";
                break;
            case 4:
                t_str << "CD";
                break;
            case 5:
                t_str << "D";
                break;
            case 6:
                t_str << "DC";
                break;
            case 7:
                t_str << "DCC";
                break;
            case 8:
                t_str << "CCM";
                break;
            case 9:
                t_str << "CM";
                break;
        }
    }
    if (digit[0] > 0)
    {
        switch (digit[0])
        {
            case 1:
                t_str << "I";
                break;
            case 2:
                t_str << "II";
                break;
            case 3:
                t_str << "III";
                break;
            case 4:
                t_str << "IV";
                break;
            case 5:
                t_str << "V";
                break;
            case 6:
                t_str << "VI";
                break;
            case 7:
                t_str << "VII";
                break;
            case 8:
                t_str << "IIX";
                break;
            case 9:
                t_str << "IX";
                break;
        }
    }
    return t_str.str();
}

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

int _fact[10] = {1, 1, 2, 6, 24, 120, 720, 5040, 40320, 362880};

void format_text(string arg_s, int arg_count)
{
    int count = 0;
    int w_count = 1;
    for (char c : arg_s)
    {
        if (c == ' '  || c == '\n')
        {
            count++;
            if (count == w_count)
            {
                count = 0;
                w_count++;
                if (w_count > arg_count)
                {
                    w_count = 1;
                }
                cout << endl;
            }   
            else
                cout << ' ';
        }
        else
            cout << c;
    }
    cout << endl;
}
/*/
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
            t_v.clear();
            n = new NiceInteger(j);
            t_v.push_back(arg);
            for (int i = 0; i < n->digit.size() - 1; i++) 
            {
                t_v.push_backcout(n->digit[i]);
            }       
            cout << j << " " << n->make_int(t_v) << endl;
            if (n->make_int(t_v) * arg == j)
            {               
                 cout << endl;
                run = false;
                cout << j << endl;
                break;
            }
        }
        multi *= 10;
        delete n;
    }
}
/*/
struct student
{
    int number;
    string fio;
};

//Преобразовать простую дробь в десятичную. Если дробь окажется периодической, то период указать в скобках. 
//Период искать в первых 100 цифрах
vector<int> p;
vector<int> per;
vector<int> t_per;

bool new_per = true;

void print_vect(vector<int> &v)
{
    for (int i : v)
            cout << i;
    cout << endl;
}

void copy_vect(vector<int> &src, vector<int> &trg)
{
    trg.clear();
    cout << endl;
    for (int i = 0; i < src.size(); i++)   
    {
        trg.push_back(src[i]);
    }
    cout << endl;
}    

bool check_per(int &i, int  &per_check, int count)
{
    bool find = true;
    if (count > 0)
    {
        find = true;
        for (int j = 0; j < count; j++)
        {
            if (p[i - count + j] != per[j])
            {
                find = false;
                break;
            }
        }
        if (find)
        {
            per_check++;
        }
        else
        {
            per_check = 0;     
            copy_vect(t_per, per);
        }
    }
    else
    {
        copy_vect(t_per, per);
    }
    return find;
}

bool mega_check_per(int index)
{
    bool rez = true;
    int idx = index;

    per.clear();
    bool find = false;

    int per_check = 0;
    int i;

    new_per = true;
    t_per.clear();
    t_per.push_back(p[index]);

    for (i = index+1; i < p.size(); i++)
    {
        if (p[i] == t_per[0])
        {   
            if (new_per)
            {
                idx = i - per.size();
                rez = check_per(i, per_check, per.size());
            }
            new_per = false;
        }
        else
            new_per = true;
        t_per.push_back(p[i]);
    }

    if (per_check > 0 && check_per(i, per_check, i - (idx + per.size())))
    {
        rez = true;
    }
    else
    {
        rez = false;
    }
    return rez;
}

void decimal()
{
    int denominator;
    int numerator;
    int remain;
    cout << "numerator: ";
    cin >> numerator; 
    cout << endl <<"denomunator: ";
    cin >> denominator;
    cout << numerator/denominator << ",";

    int per_size = 1;

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
            i++;
        }        
        numerator = remain; 
        cout << numerator/denominator;
        p.push_back(numerator/denominator);
    }
    //-------------- search period ???
    int begin_idx = 0;

    bool rez = false;
    while(rez  != true && begin_idx < 97)
    {   
        rez = mega_check_per(begin_idx);
        begin_idx++;
    }

    if (rez)
    {
        cout << endl << "find period(" << per.size() << "): ";
        for (int a : per)
            cout << a;
    }
    else
        cout << endl << "not period";

    cout << endl;
}
//Написать программу для кодирования данного текста с помощью азбуки Морзе
map<char, string> morse;

void fill_code()
{
    morse.insert({'A', ".-"});
    morse.insert({'B', "-..."});
    morse.insert({'C', "-.-."});
    morse.insert({'D', "-.."});
    morse.insert({'E', "."});
    morse.insert({'F', "..-."});
    morse.insert({'G', "--."});
    morse.insert({'H', "...."});
    morse.insert({'I', ".."});
    morse.insert({'J', ".---"});
    morse.insert({'K', "-.-"});
    morse.insert({'O', "---"});
    morse.insert({'P', ".--."});
    morse.insert({'Q', "--.-"});
    morse.insert({'R', ".-."});
    morse.insert({'S', "..."});
    morse.insert({'T', "-"});
    morse.insert({'U', "..-"});
    morse.insert({'V', "...-"});
    morse.insert({'W', ".--"});
    morse.insert({'X', "-..-"});
    morse.insert({'Y', "-.--"});
    morse.insert({'Z', "--.."});
} 

string make_morse(string arg)
{
    stringstream t_str;
    for (char i : arg)
    {
        if (i == ' ')
        {
            t_str << endl;
        }
        else
        {
            t_str << morse.at(i) << " ";
        }
    }
    return t_str.str();
}

//Два игрока по очереди выбирают по одному целому числу из отрезка [1; 10]. 
//Все выбранные числа складываются. Игра продолжается до тех пор, пока вся сумма не станет равной 100. 
//Выигрывает тот, кто сделал последний ход. 
//Напишите программу для игры с компьютером. 
//Компьютер должен придерживаться выигрышной стратегии, если она существует.

void the_game()
{
    int sum = 0;
    int a = 0;
    
    bool people_win = true;

    while (sum != 100)
    {
        while (a < 1 || a > 10)  cin >> a;
        
        sum += a;
        if (sum == 100)
           break;

        cout << "sum: " << sum << endl;

        if (100 - sum <= 10)
            a = 100 - sum;
        else
        {
            a = 9-(sum % 10);
            if (a == 0)
            {
                if  (!((sum / 10) & 1))
                    a = randint(1, 9);
                else
                    a = 10;
            }
        }
        //            a = randint(1, 10);
        sum += a;
        
        cout << "sum: " << sum << endl;
        if (sum == 100)
            people_win = false;

        a = 0;
    }
    cout << (people_win ?  "You" : "Computer");
    cout << " win" << endl;
}

void hanoy(int disk, int src, int trg, int temp)
{
    if (disk == 1)
    {
        cout << "move " << src << " => " << trg << endl;
    }
    else
    {
        hanoy (disk - 1, src, temp, trg);
        cout << "move " << src << " => " << trg << endl;
        hanoy (disk - 1, temp, trg, src);
    }
}

//Определить, можно ли расставить восемь ферзей на шахматной доске так, чтобы никакие два из них не угрожали друг другу.
int desk[8][8] = 
{
    { 0, 0, 0, 0, 0, 0, 0, 0},
    { 0, 0, 0, 0, 0, 0, 0, 0},
    { 0, 0, 0, 0, 0, 0, 0, 0},
    { 0, 0, 0, 0, 0, 0, 0, 0},
    { 0, 0, 0, 0, 0, 0, 0, 0},
    { 0, 0, 0, 0, 0, 0, 0, 0},
    { 0, 0, 0, 0, 0, 0, 0, 0},
    { 0, 0, 0, 0, 0, 0, 0, 0}
};

int queen[8][2] = 
{
    {0, 0},   
    {0, 0},   
    {0, 0},   
    {0, 0},   
    {0, 0},   
    {0, 0},   
    {0, 0},   
    {0, 0}
};

void clear_desk()
{
    for (int i = 0; i < 8; i++)
    {
        for (int j = 0; j < 8; j++)
            desk[i][j] = 0;
    }
}

int sum_diag(int idx)
{
    int rez = 0;;

    int x = queen[idx][0];
    int y = queen[idx][1];

    while (x < 7 && y < 7)
    {
            rez += desk[x+1][y+1];
            y++;
            x++;
    }    

    x = queen[idx][0];
    y = queen[idx][1];

    while (x > 0 && y > 0)
    {
        rez += desk[x-1][y-1];
        y--;
        x--;
    }    

    x = queen[idx][0];
    y = queen[idx][1];

    while (x < 7 && y > 0) 
    {
        rez += desk[x+1][y-1];
        y--;
        x++;
    }    

    x = queen[idx][0];
    y = queen[idx][1];

    while (x > 0 && y < 7)
    {
        rez += desk[x-1][y+1];
        y++;
        x--;
    }    

    return rez;
}

void set_desk()
{
    clear_desk();
    
    for (int i = 0; i < 8; i++)
    {
        desk[queen[i][0]][queen[i][1]] = 1;
    }
}

bool check_desk()
{
    for (int i = 0; i < 7; i++)
    {
        for (int j = i+1; j < 8; j++)
        if (queen[i][0] == queen[j][0] && queen[i][1] == queen[j][1])
        {
            cout << "wrong x, y" << endl;
            return false;
        }
    }

    set_desk();
    int sum;

    for (int i = 0; i < 8; i++)
    {
        sum = 0;
        for (int j = 0; j < 8; j++)
        {
            sum += desk[j][queen[i][1]];
            sum += desk[queen[i][0]][j];
            sum += sum_diag(i);
            //----- need check diag
            if (sum > 2)    return false;
        }
    }

    return true;
}

int main ()
{
    for (int i = 0; i < 8; i++) 
    {
        queen[i][0] = i;
        queen[i][1] = i;
    }

    queen[0][0] = 0;    
    queen[0][1] = 6;    

    queen[1][0] = 1;    
    queen[1][1] = 3;    

    queen[2][0] = 2;    
    queen[2][1] = 1;    

    queen[3][0] = 3;    
    queen[3][1] = 7;    

    queen[4][0] = 4;    
    queen[4][1] = 5;    

    queen[5][0] = 5;    
    queen[5][1] = 0;    

    queen[6][0] = 6;    
    queen[6][1] = 2;    

    queen[7][0] = 7;    
    queen[7][1] = 4;    

    set_desk();

    for (int i = 0; i < 8; i++)
    {
        for (int j = 0; j < 8; j++)
        {
            cout << ((desk[i][j] == 1) ? "#" : ".") << " ";
        }
        cout << endl;
    }
    cout << endl;


    cout << (check_desk() ? "Nice" : "Wrong" ) << endl;
}

