#include <iostream>
#include <sstream>
#include <fstream>
#include <iomanip>
#include <chrono>
#include <vector>
#include <algorithm>
#include <thread>
#include <stdlib.h>
#include <experimental/random>

using namespace std;

static const bool USE_LOG = false;

class Logger
{
    public:
        Logger (string f_name, bool write = true)
        {
            if (write )
                _file.open(f_name, ios::out | ios::trunc);
            else
                _file.open(f_name, ios::in);
            if (_file.bad()) this->~Logger();
        }
        ~Logger() { if (_file.is_open())    _file.close(); }

        template<typename T>
        void write(T arg, bool with_endl = true) { _file << arg; if (with_endl) _file << endl; }

        void write() { _file << _str.str(); _file << endl; _str.str(""); }
        string read() { _str.str(""); _str << _file.rdbuf(); return _str.str(); }
        
        stringstream _str;

    private:
        fstream    _file;
};

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
                matrix[i][j] = experimental::randint((int)min_value, (int)max_value);
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

template<typename T>
class DrawMatrix : public Matrix<T>
{
    public:
        DrawMatrix (int column, int row) : Matrix<T>(column, row) {}
        void view();
        void set_row_numbering (const bool arg) { row_numbering = arg; }
        void set_column_numbering (const bool arg) { column_numbering = arg; }
        void set_separate_row (const bool arg) { separate_row = arg; }
        void set_separate_column (const bool arg) { separate_column = arg; }
        void set_separate (const bool arg) { set_separate_column(arg); set_separate_row(arg); }
        void set_numbering (const bool arg)
        {
            set_row_numbering(arg);
            set_column_numbering(arg);
        }
        void set_letter_row (const bool arg) { letter_row = arg; }
        void set_letter_column (const bool arg) { letter_column = arg; }

    protected:
        static void clear_console()
        {
            if (getenv("windir") != NULL)
                system("cls");
            else
                system("clear");
        }

        int calc_w_row();

        int calc_w_column();

        int w_row;
        int w_column;

        bool row_numbering = false;
        bool column_numbering = false;
        bool separate_row = true;
        bool separate_column = true;
        bool letter_row = false;
        bool letter_column = false;
};

template<typename T>
int DrawMatrix<T>::calc_w_row()
{
    stringstream t_str;
    t_str << Matrix<T>::row;
    w_row = t_str.str().length();
    return w_row;
}

template<typename T>
int DrawMatrix<T>::calc_w_column()
{
    stringstream t_str;

    if (column_numbering && Matrix<T>::column > Matrix<T>::getExtremum())
        t_str << Matrix<T>::column;
    else
        t_str << Matrix<T>::getExtremum();
    w_column = t_str.str().length();
    return w_column;
}

template<typename T>
void DrawMatrix<T>::view()
{
    clear_console();
    calc_w_column();
    if (row_numbering)  calc_w_row();
    if (column_numbering)
    {
        if (row_numbering)
        {
            cout << setw(w_row+1) << " ";
            if (separate_column) cout << " ";
        }
        for (int i = 0; i < Matrix<T>::column; i++)
        {
            if (letter_column)
                cout << setw(w_column) << (char)('a'+i);
            else
                cout << setw(w_column) << i+1;
            if (separate_column) cout << " ";
        }
        cout << endl;
        if (row_numbering)
        {
            cout << setw(w_row+1) << " ";
            if (separate_column) cout << " ";
        }
        cout << setw(Matrix<T>::column * w_column + separate_column * Matrix<T>::column) << setfill ('_') << '_' << endl << setfill(' ');
    }
    for (int i = 0; i < Matrix<T>::row; i++)
    {
        if (separate_row) cout << endl;
        if (row_numbering)
        {
            if (letter_row)
                cout << setw(w_row) << (char)('a'+i);
            else
                cout << setw(w_row) << i+1;
            cout << "|";
            if (separate_row) cout << " ";
        }
        for (int j = 0; j < Matrix<T>::column; j++)
        {
            cout << setw(w_column) << Matrix<T>::matrix[i][j];
            if (separate_column) cout << " ";
        }
        cout << endl;
    }
    cout << endl;
}

template<typename T> class MultiMatrix
{
    public:
        MultiMatrix(int _column, int _row, int count) : size(count), row(_row), column(_column)
        {
            m_matrix = new Matrix<T>*[count];
            for (int i = 0; i < count; i++) m_matrix[i] = new Matrix<T> (column, row);
        }
        ~MultiMatrix() { delete[] m_matrix; }

        Matrix<T> **m_matrix;

        int     getSize() { return size; }
        void    fillMatrix(const T);
        void    fillMatrix(bool = false);

        int row;
        int column;

    private:
        int size;
};

template<typename T>
void MultiMatrix<T>::fillMatrix(const T arg)
{
    for (int i = 0; i < getSize(); i++)
        m_matrix[i]->fillMatrix(arg);
}

template<typename T>
void MultiMatrix<T>::fillMatrix(bool random)
{
    for (int i = 0; i < getSize(); i++)
        m_matrix[i]->fillMatrix(random);
}

template<typename T>
class DrawMultiMatrix  : public MultiMatrix<T>
{
    public:
        DrawMultiMatrix  (int column, int row, int count) : MultiMatrix<T>(column, row, count) { w_column = new int[count]; }
        ~DrawMultiMatrix()  { delete w_column; }
        void view();
        void set_row_numbering (const bool arg) { row_numbering = arg; }
        void set_column_numbering (const bool arg) { column_numbering = arg; }
        void set_separate_row (const bool arg) { separate_row = arg; }
        void set_separate_column (const bool arg) { separate_column = arg; }
        void set_separate (const bool arg) { set_separate_column(arg); set_separate_row(arg); }
        void set_numbering (const bool arg)
        {
            set_row_numbering(arg);
            set_column_numbering(arg);
        }
        void set_letter_row (const bool arg) { letter_row = arg; }
        void set_letter_column (const bool arg) { letter_column = arg; }
        void set_separate_width(int arg)    { separate_width = arg; }

    protected:
        void print_column_number(int);
        void print_column_line(int);
        void print_row(int, int);
        int  separate_width = 5;

        static void clear_console()
        {
            if (getenv("windir") != NULL)
                system("cls");
            else
                system("clear");
        }

        int     calc_w_row();
        void    calc_w_column();

        int     w_row;
        int*    w_column;

        bool    row_numbering = false;
        bool    column_numbering = false;
        bool    separate_row = true;
        bool    separate_column = true;
        bool    letter_row = false;
        bool    letter_column = false;
};

template<typename T>
int DrawMultiMatrix<T>::calc_w_row()
{
    stringstream t_str;
    t_str << MultiMatrix<T>::m_matrix[0]->row;
    w_row = t_str.str().length();
    return w_row;
}

template<typename T>
void DrawMultiMatrix<T>::calc_w_column()
{
    stringstream t_str;

    for (int i = 0; i < MultiMatrix<T>::getSize(); i++)
    {
        t_str.str("");
        if (column_numbering && MultiMatrix<T>::m_matrix[i]->column > MultiMatrix<T>::m_matrix[i]->getExtremum())
            t_str << MultiMatrix<T>::m_matrix[i]->column;
        else
            t_str << MultiMatrix<T>::m_matrix[i]->getExtremum();
        w_column[i] = t_str.str().length();
    }
}

template<typename T>
void DrawMultiMatrix <T>::print_row(int row, int m_index)
{
    if (row_numbering)
    {
        if (letter_row)
            cout << setw(w_row) << (char)('a'+row);
        else
            cout << setw(w_row) << row+1;
        cout << "|";
        if (separate_column) cout << " ";
    }
    for (int j = 0; j < MultiMatrix<T>::m_matrix[m_index]->column; j++)
    {
        cout << setw(w_column[m_index]) << MultiMatrix<T>::m_matrix[m_index]->matrix[row][j];
        if (separate_column) cout << " ";
    }
}

template<typename T>
void DrawMultiMatrix <T>::print_column_number(int index)
{
    if (row_numbering)
    {
        cout << setw(w_row+1) << " ";
        if (separate_column) cout << " ";
    }
    for (int i = 0; i < MultiMatrix<T>::m_matrix[0]->column; i++)
    {
        if (letter_column)
            cout << setw(w_column[index]) << (char)('a'+i);
        else
            cout << setw(w_column[index]) << i+1;
        if (separate_column) cout << " ";
    }
}MultiMatrix<T>
        if (separate_column) cout << "_";
    }

    cout << setw(
        MultiMatrix<T>::m_matrix[index]->column * w_column[index] + separate_column * MultiMatrix<T>::m_matrix[index]->column
    ) << setfill ('_') << '_' << setfill(' ');
}

template<typename T>
void DrawMultiMatrix <T>::view()
{
    clear_console();
    calc_w_column();
    if (row_numbering)  calc_w_row();

    if (column_numbering)
    {
        for (int i = 0; i < MultiMatrix<T>::getSize(); i++)
        {
            print_column_number(i);
            cout << setw(separate_width) << " ";
        }
        cout << endl;
        for (int i = 0; i < MultiMatrix<T>::getSize(); i++)
        {
            print_column_line(i);
            cout << setw(separate_width) << " ";
        }
        cout << endl;
    }
    for (int i = 0; i < MultiMatrix<T>::m_matrix[0]->row; i++)
    {
        if (separate_row) cout << endl;

        for (int j = 0; j < MultiMatrix<T>::getSize(); j++)
        {
            print_row (i, j);
            cout << setw(separate_width) << " ";
        }
        cout << endl;
    }
    cout << endl;
}

template<typename T>
class DrawMultiMatrix_ext  : public DrawMultiMatrix<T>
{
    public:
        DrawMultiMatrix_ext  (int column, int row) : DrawMultiMatrix<T>(column, row, 4) {}

        void calculate ();
};

template<typename T>
void DrawMultiMatrix_ext<T>::calculate()
{
    for (int y = 0; y < MultiMatrix<T>::row; y++)    
    {
        for (int x = 0; x < MultiMatrix<T>::column; x++)    
        {
            MultiMatrix<T>::m_matrix[2]->matrix[y][x] = MultiMatrix<T>::m_matrix[0]->matrix[y][x] + MultiMatrix<T>::m_matrix[1]->matrix[y][x];
            MultiMatrix<T>::m_matrix[3]->matrix[y][x] = MultiMatrix<T>::m_matrix[0]->matrix[y][x] - MultiMatrix<T>::m_matrix[1]->matrix[y][x];
        }
    }
}

int main()
{
    DrawMultiMatrix_ext<int> mtrx  = DrawMultiMatrix_ext<int> (3, 3);

    mtrx.m_matrix[0]->setMaxValue(20);
    mtrx.m_matrix[1]->setMaxValue(20);
    mtrx.m_matrix[0]->fillMatrix(true);
    mtrx.m_matrix[1]->fillMatrix(true);

    mtrx.calculate();

    mtrx.view();

    return 0;
}


