#include <iostream>
#include <sstream>
#include <fstream>
#include <iomanip>
#include <chrono>
#include <vector>
#include <algorithm>
#include <thread>
#include <stdlib.h>
//--------- keyboard --- :)
#include <unistd.h> 
#include <termios.h>

class keyboard
{
    public:
        keyboard();
        ~keyboard();
        bool kbhit();
        char getch();

    private:
        struct termios initial_settings, new_settings;
        int peek_character;
};

keyboard::keyboard()
{
    tcgetattr(0,&initial_settings);
    new_settings = initial_settings;
    new_settings.c_lflag &= ~ICANON;
    new_settings.c_lflag &= ~ECHO;
    new_settings.c_lflag &= ~ISIG;
    new_settings.c_cc[VMIN] = 1;
    new_settings.c_cc[VTIME] = 0;
    tcsetattr(0, TCSANOW, &new_settings);
    peek_character=-1;
}
    
keyboard::~keyboard()
{
    tcsetattr(0, TCSANOW, &initial_settings);
}
    
bool keyboard::kbhit()
{
    unsigned char ch;
    int nread;
    
    if (peek_character != -1) return true;

    new_settings.c_cc[VMIN]=0;
    tcsetattr(0, TCSANOW, &new_settings);
    nread = read(0, &ch, 1);
    new_settings.c_cc[VMIN]=1;
    tcsetattr(0, TCSANOW, &new_settings);

    if (nread == 1)
    {
        peek_character = ch;
        return true;
    }
    return false;
}
    
char keyboard::getch()
{
    char ch;

    if (peek_character != -1)
    {
        ch = peek_character;
        peek_character = -1;
    }
    else read(0, &ch, 1);
    return ch;
}
//------------------ keyboard

using namespace std;

static const bool USE_LOG = false;
int randint(int min_value, int max_value)
{
    return chrono::system_clock::now().time_since_epoch().count() % (max_value - min_value + 1);
}


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

struct SnakeBodyCell { int x; int y; char cell; };
enum class SnakeMove { left, right, up, down };
/*/
    Shake:
        @ - head
        o - cell body
        * - prize
        # - enemy head
/*/
class Snake
{
    public:
        Snake(char head, int m_x, int m_y) : max_x(m_x), max_y(m_y)
        {
            body.push_back({0,0,head});    
            body.push_back({0,0,'o'});    
            body.push_back({0,0,'o'});    
            _move = SnakeMove::right;
        }
        
        void set_xy(int index, int x, int y) { body[index].x = x; body[index].y = y; }
        bool move(bool);       
        
        vector<SnakeBodyCell> body;
        bool active = false;
        bool dead = false;
        SnakeMove _move;

    private:
        int max_x;
        int max_y;
};

bool Snake::move(bool circle)
{
    return true;
}


class SnakeField : public DrawMatrix<char>
{
    public:
        SnakeField (int x, int y) : DrawMatrix (x, y) 
        {
            score = 0;
            set_separate_row(false);
            
            people = new Snake('@', x, y);
            people->set_xy(0, x/2+1, y/2);
            people->set_xy(1, x/2, y/2);
            people->set_xy(2, x/2-1, y/2);
            
            set_place(people);
                
            people->active = true;
            
            enemy = new Snake('#', x, y);
        }

        ~SnakeField() 
        {
            delete people;    
            delete enemy;    
        }        
        
        Snake *people;
        Snake *enemy;
        
        void set_place(Snake *, bool empty = true);
        void start(bool = false);
        void current_view(bool);
        
        bool game_run = false;
        
        uint    score;        
};   

void SnakeField::start(bool circle)
{
    score = 0;
    game_run = true;
    current_view(circle);
}

void SnakeField::current_view(bool circle)
{
    while (game_run)    
    {
        this_thread::sleep_for(400ms);

        if (people->active) set_place(people);
        if (enemy->active) set_place(enemy, false);
        this->view();        
        score += people->body.size();
        cout << "Score: " << score << endl;

        game_run = people->move(circle);
    }
}

void SnakeField::set_place(Snake *snake, bool empty)
{
    if (empty)  fillMatrix('.');
    for (SnakeBodyCell c : snake->body) matrix[c.y][c.x] = c.cell;
} 

int main()
{
    int     run = 1;
    bool    _c = false;

    while (run != 0)
    {
        _c = (run > 1) ? true : false;
        SnakeField *sf = new SnakeField(15, 15);
        sf->start(_c);
        
        cout << "Input number for play again (0 - exit): ";
        cin >> run;
        
        delete sf;
    }

    return 0;
}
