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
    if (arg)
    {
        for (int i = 0; i < row; i++)
        {
            for (int j = 0; j < column; j++)
                matrix[i][j] = arg;
        }
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
        for (int _r = 0; _r < row; _r++)
        {
            for (int _c = 0; _c < COL_COUNT; _c++)
            {
                rand = randint(1,90);
                x = rand / 10;
                if (x == 9) x--;
                if (matrix[_r][x] == 0)
                    matrix[_r][x] = rand;
                else
                    _c--;
            }
        }

        view();

        for (int _c = 0; _c < column; _c++)
        {
            if (
                (matrix[0][_c] == 0 && matrix[1][_c] == 0  && matrix[2][_c]  == 0) ||
                (matrix[0][_c] > 0 && matrix[1][_c] > 0  && matrix[2][_c] > 0)
            )
            {
                fillMatrix((u_short)0);
                break;
            }
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
//                cout << "xx ";
                cout << setw(2) << matrix[_r][_c] << " ";
        }
        cout << endl;
    }
    cout << endl;
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

    protected:
        TicketField **field;

};

void Ticket::view()
{
    field[0]->view();
    cout << "------------------------------" << endl;
    field[1]->view();
}

int main()
{
    Ticket *t = new Ticket();

    t->view();

    delete t;

    return 0;
}