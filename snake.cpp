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
//----------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------

struct SnakeBodyCell { int x; int y; char cell; };
enum class SnakeMove { left, right, up, down };

int         sleep_count = 300;     
bool        game_run = false;
SnakeMove   s_move = SnakeMove::right;

void _snake_control()
{
    char t_char;
    keyboard *kbd = new keyboard();
    
    while (game_run)
    {
        this_thread::sleep_for(5ms);
        if (kbd->kbhit())
        {
            t_char = kbd->getch();
            switch(t_char)
            {
                case '+':   //------- speed++ 
                    sleep_count -= 25;
                    break;                
                case '-':   //------- speed--
                    sleep_count += 25;
                    break;                
                case 'w':
                    s_move = SnakeMove::up;
                    break;                
                case 's':
                    s_move = SnakeMove::down;
                    break;                
                case 'a':
                    s_move = SnakeMove::left;
                    break;                
                case 'd':
                    s_move = SnakeMove::right;
                    break;                
                case 'q':
                    game_run = false;
                    break;                
            }
        }
    }
    
    delete kbd;
}

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
        Snake(char head, int m_x, int m_y) : max_x(m_x), max_y(m_y), _head(head)    { init (); }
        bool move(bool, SnakeBodyCell &, uint &);       
        
        vector<SnakeBodyCell> body;
        bool active = false;
        bool dead = false;
        SnakeMove _move;
        char _head;
        
        void init()
        {
            body.clear();
            body.push_back({max_x/2+1, max_y/2, _head});    
            body.push_back({max_x/2, max_y/2, 'o'});    
            body.push_back({max_x/2-1, max_y/2, 'o'});    
            _move = SnakeMove::right;
        }

        bool    auto_move = false;
    private:
        int     max_x;
        int     max_y;
};

bool Snake::move(bool circle, SnakeBodyCell &prize, uint &score /*, Snake *enemy */)
{
    
    int new_x = body[0].x, new_y = body[0].y;

    if (_move == SnakeMove::left)   new_x--;
    if (_move == SnakeMove::right)   new_x++;
    if (_move == SnakeMove::up)   new_y--;
    if (_move == SnakeMove::down)   new_y++;

    if (prize.cell == '*' && new_x == prize.x && new_y == prize.y)
    {
        prize.cell = '.';
        body.push_back({0,0,'o'});
        score += 100;
    }

    for (int i = body.size()-1; i > 0; i--)
    {
        body[i].x = body[i-1].x;
        body[i].y = body[i-1].y;
    }   
    
    body[0].x = new_x;
    body[0].y = new_y;
    
    if (circle)
    {
        if (body[0].x < 0)  body[0].x = max_x - 1;
        if (body[0].x == max_x)  body[0].x = 0;
        if (body[0].y < 0)  body[0].y = max_y - 1;
        if (body[0].y == max_y)  body[0].y = 0;
    }
    else
    {
        if (body[0].x < 0 || body[0].x == max_x  || body[0].y < 0 || body[0].y == max_y) return false;
    }
    
    for (int i = 1; i < body.size(); i++)
    {
        if (body[0].x == body[i].x && body[0].y == body[i].y) return false;
    }
    
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
            set_place(people);
            people->active = true;
            enemy = new Snake('#', x, y);
            enemy->auto_move = true;
        }

        ~SnakeField() 
        {
            delete people;    
            delete enemy;    
        }        
        
        void prize_init();
        
        Snake *people;
        Snake *enemy;
        
        void set_place(Snake *, bool empty = true);
        void start(bool = false);
        void current_view(bool);
        
        SnakeBodyCell prize;
        
        uint    score;        
};   

void SnakeField::prize_init()
{
    int x, y;
    while (true)
    {
        x = randint(0, column -1);
        y = randint(0, row -1);
        
        if (matrix[y][x] == '.') break;
        
    }    
    prize.x = x;
    prize.y = y;
    prize.cell = '*';
    matrix[y][x] == '*';
}

void SnakeField::start(bool circle)
{
    score = 0;
    game_run = true;
    people->init();
    s_move = SnakeMove::right;
    prize =  { 0, 0, '.' };
    
    thread th = thread(_snake_control);
    
    current_view(circle);
    
    th.join();
}

void SnakeField::current_view(bool circle)
{
    while (game_run)    
    {
        if (people->active) 
        {
            people->_move = s_move;
            game_run = people->move(circle, prize, score);
        }
        if (game_run)    
            set_place(people);
        
        if (enemy->active) set_place(enemy, false);

        if (prize.cell == '*')
            matrix[prize.y][prize.x] = '*';
        else
        {
            if (randint(0, 100) < 10)
            {
               prize_init();
            }
        }
        this->view();        
        score += people->body.size()/5;
        cout << "Score: " << score << endl;
        this_thread::sleep_for(chrono::milliseconds(sleep_count));
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

    SnakeField *sf = new SnakeField(15, 10);
    while (run != 0)
    {
        _c = (run > 1) ? true : false;
        sf->start(_c);
        
        cout << "Input number for play again (0 - exit): ";
        cin >> run;
        
    }
    delete sf;

    return 0;
}