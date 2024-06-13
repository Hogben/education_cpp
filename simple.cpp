#include <iostream>
#include <sstream>
#include <vector>
#include <thread>
#include <chrono>

using namespace std;

int randint(int min_value, int max_value)
{
    int rez = chrono::system_clock::now().time_since_epoch().count() % (max_value - min_value + 1);
    return rez + min_value;
}

vector<int> arr_source;
vector<int> arr_odd;
vector<int> arr_even;
vector<int> arr_triple;
int answer = 0;

void check_odd()
{
    for (int i : arr_source)
    {
        if (i & 1) arr_odd.push_back(i);
        else arr_even.push_back(i);
    }   
    answer++;
}

void check_triple()
{
    for (int i : arr_source)
    {
        if (i % 3 == 0) arr_triple.push_back(i);
    }   
    answer++;
}

int main ()
{
    auto sum { [] (auto a, auto b) { return a + b; }};

    auto to_string { [] (auto x) { stringstream s; s << x; return s.str(); } };
    auto print_string { [] (string x )  { cout << x << endl; } };
    
    int size = randint(10, 20);

    for (int i = 0; i < size; i++)  arr_source.push_back(randint(10, 99));

    cout << "triple:";
    for (int i : arr_source) cout << " " << i;
    cout << endl;

    cout << sum(arr_source[0], arr_source[1]) << endl;

    print_string(to_string(2.45));

    return 0;
}
