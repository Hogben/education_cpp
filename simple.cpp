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

int main()
{
    /*/
    vector<int> mouse = {0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0};

    int count_begin = 10;

    while (mouse.size() > 1)
    {
        for (int i = 0; i < 13; i++)
        {
            count_begin++;
            if (count_begin == mouse.size())    count_begin = 0;
        }
        mouse.erase(mouse.begin() + count_begin);
    }
    
    cout << mouse[0] << endl;
    /*/
    vector<int> mouse = {1};
    int count_begin = 0;
    while (mouse.size() < 13)
    {
        for (int i = 0; i < 13; i++)
        {
            count_begin++;
            if (count_begin == mouse.size())    count_begin = 0;
        }
        mouse.insert(mouse.begin() + count_begin, 0);
    }

    for (int i : mouse)
        cout << i << " ";

    cout << endl << "=================" << endl << count_begin + 1 << endl;

    return 0;
}