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
        MultiMatrix(int column, int row, int count) : size(count)
        {
            m_matrix = new Matrix<T>*[count];
            for (int i = 0; i < count; i++) m_matrix[i] = new Matrix<T> (column, row);
        }
        ~MultiMatrix() { delete[] m_matrix; }

        Matrix<T> **m_matrix;

        int     getSize() { return size; }
        void    fillMatrix(const T);
        void    fillMatrix(bool = false);

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
}

template<typename T>
void DrawMultiMatrix <T>::print_column_line(int index)
{
    if (row_numbering)
    {
        cout << setw(w_row+1) << " ";
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

//=============================================================================================================================
enum class ShipState { untapped, wounded, dead };
struct Rect { int x1; int x2; int y1; int y2; };
Logger *_log;

struct Shot { int x; int y;};
struct SmartShot { Shot hit_begin; Shot hit_last; bool vertical = true; bool inc = true; ShipState ship_state = ShipState::untapped; };

class BattleShip
{
    public:
        BattleShip(int x, int y, int size, bool vertical = true) : x(x), y(y), size(size), vertical(vertical) { set_place(); }

        int x;
        int y;
        int size;
        bool vertical;
        Rect place;

        void set_place();

        ShipState state = ShipState::untapped;
};

void BattleShip::set_place()
{
    place.x1 = (x == 0) ? 0 : x - 1;
    place.y1 = (y == 0) ? 0 : y - 1;

    if (vertical)
    {
        place.x2 = (x == 9) ? 9 : x + 1;
        place.y2 = (y + size == 10) ? 9 : y + size;
    }
    else
    {
        place.x2 = (x + size == 10) ? 9 : x + size;
        place.y2 = (y == 9) ? 9 : y + 1;
    }
}

class Fleet
{
    public:
        Fleet(int arg) : size(arg) {
            fleet = new BattleShip *[arg];
        }
        ~Fleet() { delete fleet; }
        int getSize() { return size; }
        bool is_dead();
        BattleShip **fleet;

        void reset()
        {
            for (int i = 0; i < size; i++)  fleet[i]->state = ShipState::untapped;
        }
    private:
        int size;
};

bool Fleet::is_dead()
{
    for (int i = 0; i < size; i++)
    {
        if (fleet[i]->state != ShipState::dead) return false;
    }
    return true;
}

class FleetSeaBattle : public Fleet
{
    public:
        FleetSeaBattle(int arg) : Fleet(arg) {}

        bool check_place(int arg);
        Matrix<char>    *f_matrix;
        Matrix<char>    *enemy_matrix;
        Matrix<char>    *shot_matrix;
        
        int  check_hit(int x, int y);
        void fill_dead_place(int ship);
        
        Shot smart_shot();
        
        SmartShot s_shot;
        void smart_shot_reset();
};

void FleetSeaBattle::smart_shot_reset()
{
    s_shot = SmartShot {{0,0},{0,0},true,true,ShipState::untapped};
}

Shot FleetSeaBattle::smart_shot()
{
    Shot s;


    if (s_shot.ship_state == ShipState::untapped)    
    {
        s.x = experimental::randint(0, 9);
        s.y = experimental::randint(0, 9);
    }
    else
    {
        if (s_shot.hit_begin.x != s_shot.hit_last.x || s_shot.hit_begin.y != s_shot.hit_last.y)
        {
            if (s_shot.hit_begin.y == s_shot.hit_last.y)    s_shot.vertical = false;
        }
        else
        {
            if (
                (s_shot.hit_last.y == 9 && shot_matrix->matrix[s_shot.hit_last.y-1][s_shot.hit_last.x] == '.') ||
                (s_shot.hit_last.y == 0 && shot_matrix->matrix[s_shot.hit_last.y+1][s_shot.hit_last.x] == '.') ||
                (s_shot.hit_last.y > 0 && s_shot.hit_last.y < 9 && shot_matrix->matrix[s_shot.hit_last.y+1][s_shot.hit_last.x] == '.' && shot_matrix->matrix[s_shot.hit_last.y-1][s_shot.hit_last.x] == '.')
            )
            {
                s_shot.vertical = false;                 
                s_shot.inc = true;
            }    
        }
        
        if (s_shot.vertical)
        {
            char c = (s_shot.hit_last.y == 9) ? 'X' : shot_matrix->matrix[s_shot.hit_last.y+1][s_shot.hit_last.x];
            char c2 = (s_shot.hit_last.y == 0) ? 'X' : shot_matrix->matrix[s_shot.hit_last.y-1][s_shot.hit_last.x];
            if (USE_LOG) 
            {
                _log->_str << c << "(" << (int)c << ")";
                _log->write();
                _log->_str << c2 << "(" << (int)c2 << ")";
                _log->write();
            }
            if (s_shot.hit_last.y == 9 || shot_matrix->matrix[s_shot.hit_last.y+1][s_shot.hit_last.x] == '.')
            {
                s_shot.inc = false;
                s_shot.hit_last = s_shot.hit_begin;
            }
            
            s.x = s_shot.hit_last.x;
            s.y = s_shot.hit_last.y;
            if (s_shot.inc)
                s.y++;
            else     
                s.y--;
        }
        else
        {
            if (s_shot.hit_last.x == 9 || shot_matrix->matrix[s_shot.hit_last.y][s_shot.hit_last.x+1] == '.')
            {
                s_shot.inc = false;
                s_shot.hit_last = s_shot.hit_begin;
            }
            
            s.x = s_shot.hit_last.x;
            s.y = s_shot.hit_last.y;
            if (s_shot.inc)
                s.x++;
            else     
                s.x--;
        }
    }    
    
    return s;
}


void FleetSeaBattle::fill_dead_place(int arg)
{
    BattleShip *ship = fleet[arg];

    for (int i = ship->place.y1; i <= ship->place.y2; i++)
    {
        for (int j = ship->place.x1; j <= ship->place.x2; j++)
        {
            if (f_matrix->matrix[i][j] == ' ') 
            {
                f_matrix->matrix[i][j] = '.';
                enemy_matrix->matrix[i][j] = '.';
            }
        }
    }
}

int FleetSeaBattle::check_hit(int x, int y)
{
    int rez;
    
    for (int i = 0; i < getSize(); i++)
    {
        if (fleet[i]->place.x1 <= x && fleet[i]->place.x2 >= x && fleet[i]->place.y1 <= y && fleet[i]->place.y2 >= y)
        {
            rez = i;
            break;
        }
    }
    return rez;
}

bool FleetSeaBattle::check_place(int arg) //--- and check dead !!!!
{
    BattleShip *ship = fleet[arg];

    for (int i = ship->place.y1; i <= ship->place.y2; i++)
    {
        for (int j = ship->place.x1; j <= ship->place.x2; j++)
        {
            if (f_matrix->matrix[i][j] == '#')  return false;
        }
    }
    return true;
}
/*/
Морской бой
. - промах
# - палуба корабля
Х - пораженная палуба
/*/
class SeaBattle : public DrawMultiMatrix <char>
{
    public:
        SeaBattle(bool column_separate = false) : DrawMultiMatrix (10,10,2)
        {
            set_numbering(true);
            set_letter_column(true);
            set_separate_row(false);
            set_separate_column(column_separate);

            ai_matrix = new MultiMatrix<char> (10,10,2);
            fleet_ai = new FleetSeaBattle(10);
            fleet_people = new FleetSeaBattle(10);

            fleet_ai->f_matrix = ai_matrix->m_matrix[0];
            fleet_ai->enemy_matrix = m_matrix[1];
            fleet_ai->shot_matrix = m_matrix[0];
    
            fleet_people->f_matrix = m_matrix[0];
            fleet_people->enemy_matrix = ai_matrix->m_matrix[1];
            fleet_people->shot_matrix = ai_matrix->m_matrix[0];

            for (int i = 0; i < 10; i++)
            {
                fleet_ai->fleet[i] = new BattleShip (0, 0, ship_size[i]);
                fleet_people->fleet[i] = new BattleShip (0, 0, ship_size[i]);
            }
        }
        ~SeaBattle()
        {
            delete ai_matrix;
            delete fleet_ai;
            delete fleet_people;
        }

        MultiMatrix<char> *ai_matrix;
        FleetSeaBattle *fleet_ai;
        FleetSeaBattle *fleet_people;

        void view() { DrawMultiMatrix ::view(); };
        void battlePrepare();
        void autoPrepare(FleetSeaBattle *);
        void autoPrepare()
        {
            autoPrepare(fleet_people);
            autoPrepare(fleet_ai);
        }

        bool check_location(FleetSeaBattle *, int, bool = true);
        void set_location(FleetSeaBattle *, int);

        int input_xy();
        int input_y();
        bool start(bool arg = false);

    private:
        int ship_size[10] = { 4, 3, 3, 2, 2, 2, 1, 1, 1, 1 };
};

int SeaBattle::input_xy()
{
    int rez;
    char t_char = 32;
    
    cout << "Enter xy (a1-j10): ";
    while (!(t_char >= 'a' && t_char <= 'j'))
    {
        cin >> t_char;
    }
    
    rez = t_char - 'a';
    
    return rez;
}

int SeaBattle::input_y()
{
    int rez = -1;
    
    cout << "Enter y (1-10): ";
    while (!(rez >= 1 && rez <= 10))
    {
        cin >> rez;
    }
    rez--;
    return rez;
}

bool SeaBattle::start(bool arg) //-- autoplay
{
    bool people_step = true;
    Shot c_shot;    
    bool true_shot = false;
    FleetSeaBattle *fleet;    
    FleetSeaBattle *s_fleet;    
    
    battlePrepare();
    while (!fleet_ai->is_dead() && !fleet_people->is_dead())
    {
        true_shot = false;
        view();
        while (!true_shot)
        {
            if (people_step)
            {
                fleet = fleet_ai;
                s_fleet = fleet_people;
                if (arg)
                {
                    c_shot = s_fleet->smart_shot();
                }
                else
                {
                    c_shot.x = input_xy();
                    c_shot.y = input_y();
                }    
            }
            else
            {
                fleet = fleet_people;
                s_fleet = fleet_ai;            
                
                c_shot = s_fleet->smart_shot();
            }
            if (fleet->f_matrix->matrix[c_shot.y][c_shot.x] == ' ' || fleet->f_matrix->matrix[c_shot.y][c_shot.x] == '#') true_shot = true;
        }

        if (fleet->f_matrix->matrix[c_shot.y][c_shot.x] == ' ')
        {
            fleet->f_matrix->matrix[c_shot.y][c_shot.x] = '.';        
            fleet->enemy_matrix->matrix[c_shot.y][c_shot.x] = '.';        
            s_fleet->shot_matrix->matrix[c_shot.y][c_shot.x] = '.';            

            people_step = !people_step;
        }
        else
        {
            int ship = fleet->check_hit(c_shot.x, c_shot.y);
            
            fleet->f_matrix->matrix[c_shot.y][c_shot.x] = 'X';        
            fleet->enemy_matrix->matrix[c_shot.y][c_shot.x] = 'X';  
            s_fleet->shot_matrix->matrix[c_shot.y][c_shot.x] = 'X';            
            
            if (s_fleet->s_shot.ship_state == ShipState::untapped)
            {
                s_fleet->s_shot.hit_begin = c_shot;
                s_fleet->s_shot.ship_state = ShipState::wounded;
            }
            
            s_fleet->s_shot.hit_last = c_shot;
            
            if (fleet->check_place(ship)) //--- check dead
            {
                s_fleet->smart_shot_reset();
                fleet->fleet[ship]->state = ShipState::dead;
                fleet->fill_dead_place(ship);
            }     
        }
    }
    view();
    return people_step;
}

bool SeaBattle::check_location(FleetSeaBattle *fleet, int index, bool alarm)
{
    bool rez = true;
    BattleShip *ship = fleet->fleet[index];

    ship->set_place();
    if (ship->size > 1)
    {
        if (ship->vertical)
        {
            if (ship->y + ship->size > 10) rez = false;
        }
        else
        {
            if (ship->x + ship->size > 10) rez = false;
        }
    }
    if (rez)
    {
        rez = fleet->check_place(index);
    }
    if (!rez && alarm)
    {
        cout << "ALARM!!! Wrong location!" << endl;
        this_thread::sleep_for(chrono::seconds(2));
    }
    return rez;
}

void SeaBattle::set_location(FleetSeaBattle *fleet, int index)
{

    BattleShip *ship = fleet->fleet[index];

    if (USE_LOG && fleet == fleet_ai)
    {
        _log->_str << "ship: " << setw(2) <<index + 1 << " ==> x:" <<  (char)('a' + ship->x) << " y:" << setw(2) << ship->y + 1 << " v:" << ship->vertical << " size:" << ship->size;
        _log->write();
    }


    for (int i = 0; i < ship->size; i++)
    {
        if (ship->vertical)
        {
            fleet->f_matrix->matrix[ship->y + i][ship->x] = '#';
        }
        else
        {
            fleet->f_matrix->matrix[ship->y][ship->x + i] = '#';
        }
    }
}

void SeaBattle::autoPrepare(FleetSeaBattle *fleet)
{
    if (USE_LOG && fleet == fleet_ai) _log = new Logger("bs.log");

    for (int i = 0; i < 10; )
    {
        fleet->fleet[i]->vertical = experimental::randint(0, 1);

        if (fleet->fleet[i]->vertical)
        {
            fleet->fleet[i]->x = experimental::randint(0, 9);
            fleet->fleet[i]->y = experimental::randint(0, 10 - fleet->fleet[i]->size);
        }
        else
        {
            fleet->fleet[i]->x = experimental::randint(0, 10 - fleet->fleet[i]->size);
            fleet->fleet[i]->y = experimental::randint(0, 9);
        }

        if (check_location(fleet, i, false))
        {
            set_location(fleet, i);
            i++;
        }
    }
    if (USE_LOG && fleet == fleet_ai)  delete _log;
}

void SeaBattle::battlePrepare()
{
    int t_int = -1;

    fleet_ai->reset();
    fleet_people->reset();
    
    fillMatrix(' ');
    ai_matrix->fillMatrix(' ');
    

    view();
    cout << endl << "Ship location? (0 - auto, 1 - manual): ";
    while (!(t_int >= 0 && t_int <= 1))
    {
        cin >> t_int;
    }
    if (t_int == 1)
    {
        for (int i = 0; i < 10; i++)
        {
            DrawMultiMatrix::view();
            t_int = -1;
            cout << "Enter ship with size: " << ship_size[i] << endl;
            if (i < 6)
            {
                cout << "Use vertical? (0 - horizontal, 1 - vertial): ";
                while (!(t_int >= 0 && t_int <= 1))
                {
                    cin >> t_int;
                }
                fleet_people->fleet[i]->vertical = t_int;
            }
            fleet_people->fleet[i]->x = input_xy();
            fleet_people->fleet[i]->y = input_y();

            if (check_location(fleet_people, i))
                set_location(fleet_people, i);
            else
                i--;
        }
        autoPrepare(fleet_ai);
    }
    else
        autoPrepare();
}

int main()
{
    int     run = 1;

    SeaBattle *sb = new SeaBattle();

    while (run != 0)
    {
        if (sb->start(true))
            cout << "You win!!!" << endl << endl;
        else
            cout << "Oh!!! No!!! AI - cheeter!!!" << endl << endl;    

        cout << "Input number for play again (0 - exit): ";
        cin >> run;
    }

    delete sb;

    return 0;
}


