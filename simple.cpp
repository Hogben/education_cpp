#include <iostream>
#include <sstream>
#include <vector>
#include <thread>
#include <chrono>
#include <string>

using namespace std;

int randint(int min_value, int max_value)
{
    int rez = chrono::system_clock::now().time_since_epoch().count() % (max_value - min_value + 1);
    return rez + min_value;
}

/*/ --------------- домашка
разбить строку на символы и вывести их через print_char_code
и сформиворать новую строку из четных кодов
/*/

int sq () { return 0; };

int sq_4 () { return 2; };

int char_to_int(char arg) { return (int)arg;};

void print_char_code (char arg, int(*func_ptr)(char))
{
    cout << "symbol: " << arg << "   code: " << func_ptr(arg) << endl;
}

void printSQ (int arg, int(*func_ptr)())
{
    if (arg == 4 || arg == 0)
    {
        int i = func_ptr();
        cout << "sq(" << arg << ") = " << i << endl;
    }
    else
        cout << "value not 4" << endl;
}

int sum_string (string a, string b)
{
    return stoi(a) + stoi(b);
}

string convert_string  (int arg) 
{
    stringstream t_str;
    t_str << arg * 100;
    return t_str.str();
}

string int_to_string (int arg) 
{ 
    stringstream t_str;
    t_str << arg;
    return t_str.str();
};

string string_concat (char arg, int arg2, string(*func_ptr)(int))
{
    stringstream t_str;
    t_str << func_ptr(arg2) << " " << arg;
    return t_str.str();
}

int main ()
{
    cout << string_concat('$', 100, &int_to_string) << endl;
    cout << string_concat('P', 100, &convert_string) << endl;

    cout << string_concat('$', sum_string("34", "66"), &int_to_string) << endl;

    return 0;
}
