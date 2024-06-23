#include <iostream>
#include <sstream>
#include <vector>
#include <thread>
#include <chrono>
#include <string>
#include <cmath>

using namespace std;

int randint(int min_value, int max_value)
{
    int rez = chrono::system_clock::now().time_since_epoch().count() % (max_value - min_value + 1);
    return rez + min_value;
}

/*/ --------------- 
int
arr 1
arr 2
arr 3 = arr 1 + arr 2
arr 4 = arr 1 - arr 2
/*/

struct blabla {
    char letter;
    int  number;
};

int main ()
{
    vector<blabla> arr_1;
    vector<blabla> arr_2;
    int sum_1 = 0;
    int sum_2 = 0;
    bool e1 = false;
    
    cout << e1 << endl;

    for (int k = 0; k < 10; k++)
    {
        arr_1.push_back({(char)randint(32, 126), randint(0, 10)});
        arr_2.push_back({(char)randint(32, 126), randint(0, 10)});
    }
/**/
    for (int i = 0; i < 10; i++)
    {
        char c1 = arr_1[i].letter;
        char c2 = arr_2[i].letter;
        if ((c1 == c2 || abs(c1 - c2) == 32) &&
         ((c1 >= 'a' && c1 <='z') || (c1 >= 'A' && c1 <='Z')) && 
         ((c2 >= 'a' && c2 <='z') || (c2 >= 'A' && c2 <='Z')))
        {
            e1 = true;
            break;
        }
    } 

    cout << e1 << endl;

    cout << "arr 1: ";
    for (auto i : arr_1) cout << " " << i.letter << ":" << i.number;
    cout << endl;

    cout << "arr 2: ";
    for (auto i : arr_2) cout << " " << i.letter << ":" << i.number;
    cout << endl;

    //cout << "s1: " << sum_1 << "  s2: " << sum_2 << endl;



    (e1) ?  cout <<  "Win!!" : cout << "Try again";
    cout << endl;
    return 0;
}
