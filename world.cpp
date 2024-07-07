#include <iostream>
#include <sstream>
#include <map> 
#include <algorithm>

using namespace std;

map<string,int> world;

//------------ домашка
//------------ получить строку из файла --------
string get_from_file(string f_name)
{
    string rez;
    return rez;
}

string get_populate(string text)
{
    string rez;
    stringstream t_str;
    t_str.str("");
    for (char i : text)
    {
        if (i == ' ') 
        {
            if (t_str.str().length() > 0)
            {
                map<string,int>::iterator it = world.find(t_str.str());
                if (it == world.end())
                {
                    world.insert(
                        {t_str.str(), 1}
                        );
                }
                else
                {
                    it->second++;    
                }
            
                t_str.str("");
            }
        }
        else t_str << i;
    }
    
//    sort(world.begin(), world.end(), [](auto const a, auto const b){ return a.second > b.second;});


//    return world.begin()->first;
    int max = 1;
    rez = world.begin()->first;
    for (map<string,int>::iterator it = world.begin(); it != world.end(); it++)
    {
        if (it->second > max) 
        {  
            max = it->second;
            rez = it->first;
        }    
    }
    return rez;

}

int main()
{
    cout << get_populate("the apple is not apple phone") << endl;

    return 0;
}
