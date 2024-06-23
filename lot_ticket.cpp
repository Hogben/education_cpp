#include <iostream>
#include <sstream>
#include <fstream>
#include <iomanip>
#include <chrono>
#include <vector>
#include <algorithm>
#include <thread>
#include <stdlib.h>

using namespace std;

int randint(int min_value, int max_value)
{
    int rez = chrono::system_clock::now().time_since_epoch().count() % (max_value - min_value + 1);
    return rez + min_value;
}

template<typename T> class Matrix
{
    public:
        Matrix (int column, int row) : column(column), row(row)
        {
            matrix = new T*[row];
            for ( int i = 0; i < row; i++) matrix[i] = new T[column];
        }
        ~Matrix() { delete[] matrix; }

        void    setMaxValue(const T arg) { max_value = arg; }
        void    setMinValue(const T arg) { min_value = arg; }
        void    setExtremum(const T min, const T max) { setMinValue(min); setMaxValue(max); }
        void    fillMatrix(bool random = false);
        void    fillMatrix(const T);
        T       getExtremum(bool max = true);
        void    copy_to(Matrix<T> *);
        void    copy_from(Matrix<T> *);

        int column = 0;
        int row = 0;
        T   **matrix;

    protected:
        T   max_value = 0;
        T   min_value = 0;
};

template<typename T>
void Matrix<T>::copy_to(Matrix<T> *arg)
{
    if (row == arg->row && column == arg->column)
    {
        for (int i = 0; i < row; i++)
        {
            for (int j = 0; j < column; j++)
                arg->matrix[i][j] = matrix[i][j];
        }
    }
}

template<typename T>
void Matrix<T>::copy_from(Matrix<T> *arg)
{
    if (row == arg->row && column == arg->column)
    {
        for (int i = 0; i < row; i++)
        {
            for (int j = 0; j < column; j++)
                 matrix[i][j] = arg->matrix[i][j];
        }
    }
}

template<typename T>
T Matrix<T>::getExtremum(bool arg)
{
    vector<T> t_vct;
    for (int i = 0; i < row; i++)
    {
        for (int j = 0; j < column; j++)    t_vct.push_back(matrix[i][j]);
    }
    if (arg)
        sort(t_vct.begin(), t_vct.end(), greater<T>());
    else
        sort(t_vct.begin(), t_vct.end());
    return t_vct[0];
}

template<typename T>
void Matrix<T>::fillMatrix(bool arg)
{
    if (arg)
    {
        if (max_value == 0)
        {
            cout << "max value not define" << endl;
            return;
        }
        for (int i = 0; i < row; i++)
        {
            for (int j = 0; j < column; j++)
                matrix[i][j] = randint((int)min_value, (int)max_value);
        }
    }
    else
    {
        for (int i = 0; i < row; i++)
        {
            for (int j = 0; j < column; j++)
            {
                cout << "enter unit[" << i+1 << "][" << j+1 << "] ==> ";
                cin >> matrix[i][j];
            }
        }
    }
}

template<typename T>
void Matrix<T>::fillMatrix(T arg)
{
    for (int i = 0; i < row; i++)
    {
        for (int j = 0; j < column; j++)
            matrix[i][j] = arg;
    }
}

//=========================================================================================================================================
/*/
    1-9 10-19 20-29 30-39 40-49 50-59 60-69 70-79 80-90

    !!! ----- значения по столбцам не должны повторятся !!!!
   
/*/
static const int COL_COUNT = 5;

class TicketField : public Matrix<u_short>
{
    public: 
        TicketField() : Matrix<u_short> (9, 3) { fillMatrix((u_short)0); }

        void fill ();
        void view ();
};

void TicketField::fill()
{
    bool make_fill = true;
    u_short rand;
    int x;
    while (make_fill)
    {
        fillMatrix((u_short)0);
        for (int _r = 0; _r < row; _r++)
        {
            for (int _c = 0; _c < COL_COUNT; _c++)
            {
                rand = (u_short)randint(0,1000000)%90+1;
                x = rand / 10;
                if (x == 9) x--;
                if (matrix[_r][x] == 0)
                    matrix[_r][x] = rand;
                else
                    _c--;
            }
        }
        for (int _c = 0; _c < column; _c++)
        {
            rand = matrix[0][_c] ^ matrix[1][_c];
            rand ^= matrix[2][_c];
            if (
                (matrix[0][_c] == 0 && matrix[1][_c] == 0  && matrix[2][_c]  == 0) ||
                (matrix[0][_c] > 0 && matrix[1][_c] > 0  && matrix[2][_c] > 0 ) ||
                rand == 0
            )
                break;
            else
            {
                if (_c == 8)    make_fill = false;
            }
        }
    }
}

void TicketField::view()
{
    for (int _r = 0; _r < row; _r++)
    {
        for (int _c = 0; _c < column; _c++)
        {
            if (matrix[_r][_c] == 0)
                cout << "   ";
            else 
                cout << setw(2) << matrix[_r][_c] << " ";
        }
        cout << endl;
    }
//      cout << endl;
}

class Ticket
{
    public: 
        Ticket() 
        { 
            field = new TicketField*[2]; 
            field[0] = new TicketField();
            field[1] = new TicketField();
            field[0]->fill();
            field[1]->fill();
        } 
        ~Ticket() { delete[] field; } 

        void view();
        TicketField **field;
};

void Ticket::view()
{
    field[0]->view();
    cout << "------------------------------" << endl;
    field[1]->view();
}

class PlayLottery
{
    public:     
        PlayLottery (int count) : size(count), game(0)
        {
            ticket = new Ticket*[count];
            for (int i = 0; i < count; i++) ticket[i] = new Ticket();
        }
        ~PlayLottery() { delete[] ticket; }

        Ticket **ticket;
        vector<u_short> play_number;

        void check_jeckpot(u_short num);
        void get_number(u_short num);
        void play();
        void view();

    protected:
        bool check_column();
        bool check_field();
        bool check_ticket();
        
        int size;
        int game;
};

void PlayLottery::view()
{
    for (int i = 0; i < size; i++)
    {
        cout << "ticket " << i+1 << endl;
        ticket[i]->view();
    }
    cout << "=======================" << endl;
}

void PlayLottery::play()
{
    bool try_num;
    u_short c_num;
    cout << "start:";
    while (game < 3)
    {
        try_num = true;
        while (try_num)
        {
            c_num = randint(1,90);
            if (find(play_number.begin(), play_number.end(), c_num) == play_number.end())  try_num = false;
        }
        play_number.push_back(c_num);
        cout << " " << c_num;
        check_jeckpot(c_num);
    }
}

void PlayLottery::get_number(u_short num)
{
    bool find = false;
    for (int i = 0; i < size; i++)
    {
        for (int _f = 0; _f < 2; _f++)
        {
            find = false;
            for (int _r = 0; _r < ticket[i]->field[_f]->row; _r++)
            {
                for (int _c = 0; _c < ticket[i]->field[_f]->column; _c++)
                {
                    if (ticket[i]->field[_f]->matrix[_r][_c] == num)
                    {
                        find = true;
                        ticket[i]->field[_f]->matrix[_r][_c] = 0;
                        break;      
                    }
                }
                if (find)   break;
            }
        }
    }
}

void PlayLottery::check_jeckpot(u_short num)
{
    get_number(num);
    switch (game)
    {
    case 0:
        if (check_column()) game++;
        break;
    case 1:
        if (check_field()) game++;
        break;
    case 2:
        if (check_ticket())  game++;;
        break;
    }
}

bool PlayLottery::check_column()
{
    bool rez = false;
    bool find = false;
    u_short sum;
    for (int i = 0; i < size; i++)
    {
        for (int _f = 0; _f < 2; _f++)
        {
            find = false;
            for (int _r = 0; _r < ticket[i]->field[_f]->row; _r++)
            {
                sum = 0;
                for (int _c = 0; _c < ticket[i]->field[_f]->column; _c++)
                {
                    sum += ticket[i]->field[_f]->matrix[_r][_c];
                }
                if (sum == 0)
                {
                    find = true;                    
                    cout  << endl << "ticket: " << i + 1 << " win in game 1!!!" << endl;
                    rez = true;
                    break;
                }
            }
            if (find)   break;
        }
    }
    return rez;
}

bool PlayLottery::check_field()
{
    bool rez = false;
    u_short sum;
    for (int i = 0; i < size; i++)
    {
        for (int _f = 0; _f < 2; _f++)
        {
            sum = 0;
            for (int _r = 0; _r < ticket[i]->field[_f]->row; _r++)
            {
                for (int _c = 0; _c < ticket[i]->field[_f]->column; _c++)
                {
                    sum += ticket[i]->field[_f]->matrix[_r][_c];
                }
            }
            if (sum == 0)
            {
                cout  << endl << "ticket: " << i + 1 << " win in game 2!!!" << endl;
                rez = true;
                break;
            }
        }
    }
    return rez;
}

bool PlayLottery::check_ticket()
{
    bool rez = false;
    u_short sum;
    for (int i = 0; i < size; i++)
    {
        for (int _f = 0; _f < 2; _f++)
        {
            sum = 0;
            for (int _r = 0; _r < ticket[i]->field[_f]->row; _r++)
            {
                for (int _c = 0; _c < ticket[i]->field[_f]->column; _c++)
                {
                    sum += ticket[i]->field[_f]->matrix[_r][_c];
                }
            }
        }
        if (sum == 0)
        {
            cout  << endl << "ticket: " << i + 1 << " win in game 3!!!" << endl;
            rez = true;
            break;
        }
    }
    return rez;
}

int main()
{
    PlayLottery *pl = new PlayLottery(10);
    pl->view();
    pl->play();
    delete pl;
    return 0;
}