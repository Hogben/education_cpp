#include <iostream>
#include <sstream>
#include <vector>
#include <thread>
#include <chrono>
#include <string>
#include <cmath>
#include <map>

using namespace std;

int randint(int min_value, int max_value)
{
    int rez = chrono::system_clock::now().time_since_epoch().count() % (max_value - min_value + 1);
    return rez + min_value;
}

map<wchar_t, string> t_letter;
//----- домашка разобраться с русскими буквами в строке wchar_t;

string tranc(string s_in)
{
    stringstream rez;
    for (wchar_t t_ch : s_in)
    {
        rez << t_letter.at(t_ch);
    }
    return rez.str();
}

map<int, string> people;
map<int, string> staff;
map<int, int> _ref;

int main ()
{
    setlocale(LC_ALL, "Russian"); // 0, ""

    t_letter.insert({' ', " "});
    t_letter.insert({'а', "a"});
    t_letter.insert({'б', "b"});
    t_letter.insert({'в', "v"});
    t_letter.insert({'г', "g"});
    t_letter.insert({'д', "d"});
    t_letter.insert({'е', "e"});
    t_letter.insert({'ё', "yo"});
    t_letter.insert({'ж', "zh"});
    t_letter.insert({'з', "z"});
    t_letter.insert({'и', "i"});
    t_letter.insert({'й', "j"});
    t_letter.insert({'к', "k"});
    t_letter.insert({'л', "l"});
    t_letter.insert({'м', "m"});
    t_letter.insert({'н', "n"});
    t_letter.insert({'о', "o"});
    t_letter.insert({'п', "p"});
    t_letter.insert({'р', "r"});
    t_letter.insert({'с', "s"});
    t_letter.insert({'т', "t"});
    t_letter.insert({'у', "u"});
    t_letter.insert({'ф', "f"});
    t_letter.insert({'х', "h"});
    t_letter.insert({'ц', "c"});
    t_letter.insert({'ч', "ch"});
    t_letter.insert({'ш', "sh"}); 
    t_letter.insert({'щ', "shh"});
    t_letter.insert({'ь', "`"});
    t_letter.insert({'ы', "y"});
    t_letter.insert({'ъ', "`"});
    t_letter.insert({'э', "ye"});
    t_letter.insert({'ю', "yu"});
    t_letter.insert({'я', "ya"});
    
    string s = "";

    while (s != "0")
    {
        cin >> s;
        cout << tranc(s);
    }
/*/

    people.insert({1, "Jony"});
    people.insert({2, "Bob"});
    people.insert({3, "Sara"});
    people.insert({4, "Jony"});
    people.insert({5, "Alex"});
    people.insert({6, "Akim"});

    staff.insert({1, "manager"});
    staff.insert({2, "worker"});
    staff.insert({3, "driver"});

    _ref.insert({1, 1});
    _ref.insert({2, 3});
    _ref.insert({3, 1});
    _ref.insert({4, 2});
    _ref.insert({5, 3});
    _ref.insert({6, 1});


    for (int i = 1; i <= 6; i++) 
        cout << people.at(i) << " ==> " << staff.at(_ref.at(i)) << endl;

/*/
 

    return 0;
}
