<?php


function getCalendarEvents() {
    global $conn;
    
    $sql = "SELECT 
        order_date,
        status,
        COUNT(*) as order_count
    FROM orders 
    WHERE order_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
    GROUP BY order_date, status";
    
    $result = $conn->query($sql);
    $events = [];
    
    while($row = $result->fetch_assoc()) {
        $className = '';
        switch($row['status']) {
            case 'Complete':
                $className = 'fc-event-completed';
                break;
            case 'Pending':
                $className = 'fc-event-pending';
                break;
            case 'Cancelled':
                $className = 'fc-event-cancelled';
                break;
            case 'Refunded':
                $className = 'fc-event-refunded';
                break;
        }
        
        $events[] = [
            'title' => $row['order_count']. ' ' . $row['status']  . ' Orders',
            'date' => $row['order_date'],
            'className' => $className
        ];
    }
    
    return $events;
}