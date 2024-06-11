
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
    int rez = chrono::system_clock::now().time_since_epoch().count() % (max_value - min_value + 1);
    return rez + min_value;
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

//=========================================================================================================================================

struct SnakeBodyCell { int x; int y; char cell; };
enum class SnakeMove { left, right, up, down };

int         sleep_count = 400;     
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
        bool move(bool, SnakeBodyCell &, uint &, Snake *);       
        bool check_snake(bool, int , int , SnakeBodyCell &);
        
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


bool Snake::check_snake(bool circle, int new_x, int new_y, SnakeBodyCell &prize)
{
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

bool Snake::move(bool circle, SnakeBodyCell &prize, uint &score , Snake *enemy)
{
    int new_x = body[0].x, new_y = body[0].y;

    if (auto_move)
    {
        //------- check infinity and move to prize --- 
        vector<SnakeBodyCell> t_snake = body;      
        int rand_v;
        int rand_inc;
        bool move_to_prize = false;

        while (true)
        {
            body = t_snake;
            new_x = body[0].x;
            new_y = body[0].y;
            rand_v = randint(0,1);
            rand_inc = randint(0,1);
            move_to_prize = false;
            
            if (prize.cell == '*')
            {
                move_to_prize = true;
                if (body[0].y == prize.y)
                {
                    if (body[0].x > prize.x) 
                        new_x--;
                    else
                        new_x++;
                }
                else
                {
                    if (body[0].y > prize.y) 
                        new_y--;
                    else
                        new_y++;
                }

                for (int i = 1; i < body.size(); i++)
                {
                    if (new_x == body[i].x && new_y == body[i].y) 
                    {
                        new_x = body[0].x;
                        new_y = body[0].y;
                        move_to_prize = false;
                        break;    
                    }
                }
            }
            if (!move_to_prize)
            {
                if(rand_v) 
                {
                    if (rand_inc)   new_y++;
                    else            new_y--;
                        
                }    
                else
                {
                    if (rand_inc)   new_x++;
                    else            new_x--;
                }
            }
            if (check_snake(circle, new_x, new_y, prize))
            {
                break;
            }
        }
        for (int i = 0; i < enemy->body.size(); i++)
        {
            if (body[0].x == enemy->body[i].x && body[0].y == enemy->body[i].y) active = false;
        }
    }
    else
    {
        if (_move == SnakeMove::left)   new_x--;
        if (_move == SnakeMove::right)   new_x++;
        if (_move == SnakeMove::up)   new_y--;
        if (_move == SnakeMove::down)   new_y++;
        
        if (check_snake(circle, new_x, new_y, prize))
        {
            if (enemy->active)
            {
                for (int i = 0; i < enemy->body.size(); i++)
                {
                    if (body[0].x == enemy->body[i].x && body[0].y == enemy->body[i].y) return false;
                }
            }
        }
        else
            return false;
    }

    if (prize.cell == '*' && new_x == prize.x && new_y == prize.y)
    {
        prize.cell = '.';
        body.push_back({body[body.size()-1].x,body[body.size()-1].y,'o'});
        if (auto_move)
        {
            if (score > 200)
                score -= 200;
            else    
                score = 0;
        }
        else
            score += 100;
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
        void enemy_init();
        bool check_inf(int x, int y, bool circle);
       
        SnakeBodyCell prize;
        
        uint    score;        
};   

struct Point { int x; int y; };

bool SnakeField::check_inf(int x, int y, bool circle)  
{
    vector<Point> v;

    v.push_back({x + 1, y});
    v.push_back({x - 1, y});
    v.push_back({x, y - 1});
    v.push_back({x, y + 1});

    if (circle)
    {
        for (auto &x : v)
        {
            if (x.x < 0)        x.x = column - 1;
            if (x.x == column)  x.x = 0;
            if (x.y < 0)        x.y = row - 1;
            if (x.y == row)     x.y = 0;
        }
    }
    else
    {
        bool find = true;
        while (find)
        {
            int i = 0;
            for (; i < v.size(); i++)
            {
                if (v[i].x < 0 || v[i].x == column || v[i].y < 0 || v[i].y == row)
                {
                    v.erase(v.begin()+i);
                    break;
                }
            }
            if (i == v.size())  find = false;
        }
/*/
        v.erase(
            remove_if(
                v.begin(),
                v.end(),
                [](auto i){ return i.x < 0 || i.x == column || i.y < 0 || i.y == row;} ),
            v.end()
        );
/*/
    }

    for (auto x : v)
    {
        if (matrix[x.y][x.x] == '.' || matrix[x.y][x.x] == '*') return true;
    }
    return false;
}

void SnakeField::enemy_init()
{
    enemy->init();
    if (matrix[0][0] == '.' && matrix[1][0] == '.' && matrix[2][0] == '.')
    {
        for (int i = 0; i < 3; i++)
        {
            enemy->body[i].x = 0;
            enemy->body[i].y = i;
        }
        enemy->active = true;
        return;
    }
    if (matrix[0][column-1] == '.' && matrix[1][column-1] == '.' && matrix[2][column-1] == '.')
    {
        for (int i = 0; i < 3; i++)
        {
            enemy->body[i].x = column-1;
            enemy->body[i].y = i;
        }
        enemy->active = true;
        return;
    }
    if (matrix[row-1][0] == '.' && matrix[row-2][0] == '.' && matrix[row-3][0] == '.')
    {
        for (int i = 0; i < 3; i++)
        {
            enemy->body[i].x = 0;
            enemy->body[i].y = row - i - 1;
        }
        enemy->active = true;
        return;
    }
    if (matrix[row-1][column-1] == '.' && matrix[row-2][column-1] == '.' && matrix[row-3][column-1] == '.')
    {
        for (int i = 0; i < 3; i++)
        {
            enemy->body[i].x = column-1;
            enemy->body[i].y = row - i - 1;
        }
        enemy->active = true;
        return;
    }
}


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
    int step = 0;
    enemy->active = false;
    while (game_run)    
    {
        if (step == 15)
        {
            step = 0;
            if (!enemy->active)
            {
                if (randint(0, 1) == 1) enemy_init();
            }
        }
        if (people->active) 
        {
            people->_move = s_move;
            game_run = people->move(circle, prize, score, enemy);
        }
        
        if (enemy->active) 
        {
            if (check_inf(enemy->body[0].x, enemy->body[0].y, circle))
                enemy->move(circle, prize, score, people);
            else
                enemy->active = false;
        }

        if (game_run)    
        {
            set_place(people);
            if (enemy->active) set_place(enemy, false);

            if (prize.cell == '*')
                matrix[prize.y][prize.x] = '*';
            else
            {
                if (randint(0, 100) < 10)   prize_init();
            }
            this->view();        
            score += people->body.size()/5;
            cout << "Score: " << score << endl;
            this_thread::sleep_for(chrono::milliseconds(sleep_count));
            step++;
        }
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