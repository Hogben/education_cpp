import fdb

_host = '178.237.185.54'  # Хост базы данных
_database = '/home/firebird/akim.fdb'  # Путь к файлу базы данных
_user = 'testuser'  # Имя пользователя
_password = '012345'  # Пароль
_port = 30503  # Порт (по умолчанию 

try:
    # Подключение к базе данных
    connection = fdb.connect(
        host=_host,
        database=_database,
        user=_user,
        password=_password,
        port=_port
    )

    cursor = connection.cursor()

    print ('table CCATEGORIES')
    cursor.execute("SELECT * FROM CATEGORIES")
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    print ('table CUSTOMERS')
    cursor.execute("SELECT * FROM CUSTOMERS")
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    print ('table ORDERS')
    cursor.execute('select * from ORDERS')
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    print ('table PRODUCTS')
    cursor.execute('select * from PRODUCTS')
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    print ('table SUPPLIERS')
    cursor.execute('select * from SUPPLIERS')
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    query = 'update CUSTOMERS set EMAIL = ?, ADRESS = ? where c_id = ?'

    cursor.execute(query, ('user_123@ya.ru','Уфа, ул.Мира, 12, кв.143'.encode('utf8'), 1))
    cursor.execute(query, ('elena@mail.ru','Тюменская обл., с.Зизуля, ул.Советская, 2'.encode('utf8'), 2))

    cursor.execute('update CUSTOMERS set DISCOUNT = ? where c_id = ?', (5, 2))

    connection.commit()

    print ('\nget orders info w/o discount')
    sql = '''
select
    p.name,
    o.order_date,
    o.order_count,
    cast(o.order_count*p.price as numeric(15,2)),
    c.phone,
    c.name
from orders o
join products p on p.prod_id = o.id_product
join customers c on c.c_id = o.id_customer
    '''
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
       encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
       print(encoded_row)

    print ('\nget orders info with discount')
    sql = '''
select
    p.name,
    o.order_date,
    o.order_count,
    case when (c.discount = 0 or c.discount is null) then cast(o.order_count*p.price as numeric(15,2)) else cast(o.order_count*(p.price*(100-c.discount)/100) as numeric(15,2)) end,
    c.phone,
    c.name
from orders o
join products p on p.prod_id = o.id_product
join customers c on c.c_id = o.id_customer
    '''
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
       encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
       print(encoded_row)

    print ('\nget goods input by suppliers')
    sql = '''
select
 s.name,
 p.name,
 p.stock_count
from suppliers s
join products p on p.supplier_id = s.sup_id
    '''
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
       encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
       print(encoded_row)

    print ('\nget orders info by month')
    sql = '''
select
    'февраль', 
    p.name,
    sum(o.order_count)
from orders o
join products p on p.prod_id = o.id_product
where o.order_date >= '01.02.2025' and o.order_date < '01.03.2025'
group by 1, 2
union all
select
    'март',
    p.name,
    sum(o.order_count)
from orders o
join products p on p.prod_id = o.id_product
where o.order_date >= '01.03.2025' and o.order_date < '01.04.2025'
group by 1, 2
order by 1 desc, 2
    '''
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [row[0]] + [row[1].encode('cp1251').decode('utf8')] + [row[2]]
        print(encoded_row)


except fdb.Error as e:
    connection.rollback()
    print(f"Ошибка при работе с базой данных: {e}")

finally:
    # Закрытие соединения
    if 'connection' in locals() and connection:
        connection.close()