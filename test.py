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

#    query = 'update CUSTOMERS set EMAIL = ?, ADRESS = ? where c_id = ?'
#
#    cursor.execute(query, ('user_123@ya.ru','Уфа, ул.Мира, 12, кв.143'.encode('utf8'), 1))
#    cursor.execute(query, ('elena@mail.ru','Тюменская обл., с.Зизуля, ул.Советская, 2'.encode('utf8'), 2))
#
#    cursor.execute('update CUSTOMERS set DISCOUNT = ? where c_id = ?', (5, 2))
#
#    connection.commit()

    sql = 'select * from categories'
    print ('\n-----\n' + sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    sql = "select c.cat_name from categories c where c.cat_id > 3"
    print ('\n-----\n' + sql)
    cursor.execute("select c.cat_name from categories c where c.cat_id > 3")
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    sql = 'select c.cat_name from categories c where c.cat_name like (\'_х%\')'
    print ('\n-----\n' + sql)
    cursor.execute(sql.encode('utf8'))
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    sql = "select c.cat_name from categories c where c.cat_name like ('%о% %ва%')"
    print ('\n-----\n' + sql)
    cursor.execute(sql.encode('utf8'))
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    sql = '''select
    p.name,
    p.price,
    o.order_date,
    o.order_count
from products p
left join orders o on o.id_product = p.prod_id
where p.price between 125 and 1750'''
    print ('\n-----\n' + sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)


    sql =  '''select
    p.name,
    p.price,
    o.order_date,
    o.order_count
from products p
inner join orders o on o.id_product = p.prod_id
where p.price between 125 and 1750
order by p.prod_id, o.order_date desc'''
    print ('\n-----\n' + sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)


    sql =  '''select
    p.name,
    p.price,
    o.order_date,
    cast(case when o.order_count*p.price is null then 0 else o.order_count*p.price end as numeric(15,2))
from products p
left join orders o on o.id_product = p.prod_id'''
    print ('\n-----\n' + sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    sql =  '''select
    p.name,
    p.price,
    cast(sum(case when o.price_at_order is null then o.order_count*p.price else o.order_count*o.price_at_order end) as numeric(15,2))
from orders o
join products p on p.prod_id = o.id_product
where p.price > 200 
group by 1,2
having sum(case when o.price_at_order is null then o.order_count*p.price else o.order_count*o.price_at_order end) >= 20000'''
    print ('\n-----\n' + sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    sql = '''select
    p.name,
    o.order_date,
    o.order_count,
    cast(o.order_count*p.price as numeric(15,2)),
    c.phone,
    c.name
from orders o
join products p on p.prod_id = o.id_product
join customers c on c.c_id = o.id_customer'''
    print ('\n-----\n' + sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
       encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
# encoded_row = [row[0]] + [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row[1:]]
       print(encoded_row)

    sql = '''select
    p.name,
    o.order_date,
    o.order_count,
    case when (c.discount = 0 or c.discount is null) then cast(o.order_count*p.price as numeric(15,2)) else cast(o.order_count*(p.price*(100-c.discount)/100) as numeric(15,2)) end,
    c.phone,
    c.name
from orders o
join products p on p.prod_id = o.id_product
join customers c on c.c_id = o.id_customer'''
    print ('\n-----\n' + sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    for row in rows:
       encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
       print(encoded_row)

    print ('\n-----\nget orders info by month')
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
    cursor.execute(sql.encode('utf8'))
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
#        encoded_row = [row[0]] + [row[1].encode('cp1251').decode('utf8')] + [row[2]]
        print(encoded_row)

    print ('\n-----\nsome test')
    sql = '''
select
    cast (sum(o.order_count*p.price) as numeric(15,2))
from orders o
join products p on p.prod_id = o.id_product
where p.prod_id <> 3
    '''
    cursor.execute(sql.encode('utf8'))
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    print ('\n-----\nsome test')
    sql = '''
select
    cast (sum(o.order_count*p.price) as numeric(15,2))
from orders o
join products p on nullif(p.prod_id,3) = o.id_product
    '''
    cursor.execute(sql.encode('utf8'))
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)

    print ('\n-----\navg orders by month')
    sql = '''
select
'02',
avg(total)
from
(
select
o.id,
cast(sum(o.order_count * p.price) as numeric(15, 2)) as total    
from orders o
join products p on p.prod_id = o.id_product
where o.order_date >= '01.02.2025' and o.order_date < '01.03.2025'
group by 1
)
union all
select
'03',
avg(total)
from
(
select
o.id,
cast(sum(o.order_count * p.price) as numeric(15,2)) as total    
from orders o
join products p on p.prod_id = o.id_product
where o.order_date >= '01.03.2025' and o.order_date < '01.04.2025'
group by 1
)
    '''
    cursor.execute(sql.encode('utf8'))
    rows = cursor.fetchall()
    for row in rows:
        encoded_row = [item.encode('cp1251').decode('utf8') if isinstance(item, str) else item for item in row]
        print(encoded_row)


except fdb.Error as e:
    connection.rollback()
    print(f"Ошибка при работе с базой данных: {e}")

finally:
    # Закрытие соединения
    if 'connection' in locals() and connection:
        connection.close()

'''
select
avg(total),
avg(true_sum),
(select ...) limit 1
from
(
select 
vp.num_doc, 
sum(vp.view_order_money * vp.order_count) as total,
sum(vp.view_order_money * vp.remainder_count) as true_sum
from view_payed_orders vp
group by 1
)



select
    o.id,
    o.order_date, - month from date
    sum(o.order_count*p.price)
from orders o
join products p on p.prod_id = o.id_product
group by 1,2
order by 2

EXTRACT(MONTH FROM date_column)

select
EXTRACT(MONTH FROM o.order_date) as month_,
avg(o.order_count*p.price)
from orders o
join products p on p.prod_id = o.id_product
group by 1
order by 1   

where EXTRACT(year FROM o.order_date) = 2025

select
    '' || EXTRACT(YEAR FROM o.order_date) as yr,
    EXTRACT(MONTH FROM o.order_date) as mnth,
    avg(o.order_count*p.price) as midle_sum
from orders o
join products p on p.prod_id = o.id_product
group by 1,2
order by 1,2

select
    EXTRACT(MONTH FROM o.order_date),
    avg(o.order_count*p.price)
from orders o
join products p on p.prod_id = o.id_product
where o.order_date containing (2025)
group by 1

покупатели или продукты который в телефоне покупателя или в цене продукта содержат 123
имя покупателя, телефон, продукт, цена

select
c.name,
c.phone,
p.name,
p.price
from customers c
join products p on 1=1
where c.phone || p.price containing (123)


select
    c.name,
    c.phone,
    p.name,
    p.price
from customers c
join products p on 1=1 and p.price containing (123)
where c.phone containing (123)

select
    c.name,
    c.phone,
    c.adress,
    p.name,
    p.price
from customers c
join products p on 1=1 and p.price containing (123)
where c.phone || c.adress containing (123)

n8n

тип продукции / катергории / товар / кол-во / сумма




select t.name, c.name, g.name, sum(vg.order_count), sum(vg.order_count * vg.view_order_money)
from type_goods t
left join content_goodg c on c.id_type = t.id
left join goods g on g.id_content_goods = c.id
left join view_payed_orders vg on vg.goods_id = g.id
group by 1, 2, 3 
having sum(vg.order_count) > 9
order by 1, 2, 3

'''

input()
