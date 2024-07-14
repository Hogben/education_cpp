#include <iostream>
#include <sstream>
#include <map> 
#include <algorithm>
#include <fstream>

using namespace std;

map<string,int> world;

//------------ домашка
//------------ получить строку из файла --------
string get_from_file(string f_name)
{
//    string rez;
    stringstream t_str;
    ifstream _file(f_name);

    t_str << _file.rdbuf();
    /*/
    while (getline(_file, t_str)) 
    {
        rez += t_str;
        rez += "\n";
    }
    /*/
    return t_str.str();
}

// " ", ".", ","

string get_populate(string text)
{
    string rez;
    stringstream t_str;
    t_str.str("");
    for (char i : text)
    {
        if (i == ' ' || i == '.' || i == ',') 
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
    cout << get_populate(get_from_file("1.txt")) << endl;
//    cout << get_from_file("home_task.txt") << endl;

    return 0;
}
