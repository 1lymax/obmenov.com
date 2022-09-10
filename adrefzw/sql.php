delete
FROM orders
WHERE orders.id
IN (

SELECT orders.id
FROM orders, payment
WHERE orders.ordered =0
AND payment.ordered =0
AND payment.canceled =0
AND payment.orderid = orders.id
)



delete FROM `clients` WHERE nikname='' and purse_z='' and purse_u='' and purse_r='' and fname='' and wmid='' and email='' and purse_LRUSD='' and purse_PMUSD='' and (partnerid=299 or partnerid=0)



обновление попул€рных направлений

insert into popular(currin,currout,_currin,_currout, count) SELECT orders.currin, orders.currout, (

SELECT extname
FROM currency
WHERE name = orders.currin
) AS _currin, (

SELECT extname
FROM currency
WHERE name = orders.currout
) AS _currout, count( orders.id ) AS count
FROM orders, payment
WHERE payment.orderid = orders.id
AND payment.canceled =1
AND orders.ordered =1
GROUP BY currin, currout
ORDER BY count DESC



===================
контроль неправильныйх транз. партнера
SELECT orders.id, orders.attach, payment.id, payment_out.id, payment.LMI_PAYER_WM
FROM orders, payment_out, payment
WHERE orders.id = payment_out.payment
AND orders.id = payment.orderid
AND payment_out.partnerid =1982
AND payment_out.retval =0
AND payment.ordered =1
LIMIT 0 , 30 


====================
баланс партнера 

SELECT (

SELECT SUM( bonus ) AS sum
FROM partner_bonus
WHERE partnerid =1982
), (

SELECT SUM( orders.attach ) AS total_payed
FROM orders, payment_out, payment
WHERE orders.id = payment_out.payment
AND orders.id = payment.orderid
AND payment_out.partnerid =1982
AND payment_out.retval =0
AND payment.ordered =1
GROUP BY payment_out.partnerid
) 


выгрузка итогов по ордерам
insert into i (sum, clid, t) (SELECT SUM(attach) as sum, orders.clid, left(orders.time,4)
					FROM orders, payment 
					WHERE orders.ordered=1 
					AND orders.authorized=1
					AND payment.ordered=1
					AND payment.canceled=1
and ( orders.time like '%2011%' )
					AND orders.id=payment.orderid group by orders.clid)


insert into i_p(cl_count, pid, t) (SELECT COUNT(DISTINCT orders.clid) as sum, partnerid, '2011' FROM orders, payment 
			WHERE payment.orderid=orders.id 
			AND payment.ordered=1 
			AND payment.canceled=1 and left(orders.time,4)='2011'
			group by orders.partnerid)



SELECT (
SELECT COUNT( DISTINCT orders.clid ) AS sum
FROM orders, payment
WHERE payment.orderid = orders.id
AND payment.ordered =1
AND payment.canceled =1
AND LEFT( orders.time, 4 ) =  '2012'
AND orders.partnerid =299
) + ( 
SELECT SUM( cl_count ) 
FROM i_p
WHERE pid =299 ) AS total


 

cat /var/log/nginx/error.log | grep "limiting requests" > /root/ip.ban/output.txt
cat /dev/null > /root/ip.ban/iptables_ban.sh
awk -F:  '{print $6}' /root/ip.ban/output.txt | awk -F, '{print $1}' | sort | uniq -c | awk '{print $2 }' > /root/ip.ban/ban.ip.list
awk '{print "iptables -A INPUT -p tcp --dport 80 -s " $1 " -j DROP" }' /root/ip.ban/ban.ip.list | head -n 50 >> /root/ip.ban/iptables_ban.sh
cat /root/iptables_ban.sh
cat /dev/null > /var/log/nginx/error.log



SELECT SUM( summin ) 
FROM orders, payment_out
WHERE (
orders.currin =  'P24UAH'
AND orders.currout =  'WMU'
)
AND orders.id = payment_out.payment
AND LENGTH( payment_out.purse ) >4
AND orders.time LIKE  '%2010%'