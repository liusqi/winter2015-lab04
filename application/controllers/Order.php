<?php

/**
 * Order handler
 * 
 * Implement the different order handling usecases.
 * 
 * controllers/welcome.php
 *
 * ------------------------------------------------------------------------
 */
class Order extends Application {

    function __construct() {
        parent::__construct();
    }

    // start a new order
    function neworder() {
        //FIXME
        $order_num = $this->orders->highest() + 1;
        
        date_default_timezone_set("America/Vancouver");
        
        $new = $this->orders->create();
        $new->num = $order_num;
        $new->date = date("Y/n/d/H/i/s");
        $new->status = "a";
        $new->total = 0;
        
        $this->orders->add($new);
        
        $this->display_menu($order_num);
    }

    // add to an order
    function display_menu($order_num = null) {
        if ($order_num == null)
            redirect('/order/neworder');

        $this->data['pagebody'] = 'show_menu';
        $this->data['order_num'] = $order_num;
        $this->data['title'] = "Order number: " . $order_num . " Total: " . $this->orders->get($order_num)->total;

        // Make the columns
        $this->data['meals'] = $this->make_column('m');
        $this->data['drinks'] = $this->make_column('d');
        $this->data['sweets'] = $this->make_column('s');
        
        foreach($this->data['meals'] as $meal)
        {
            $meal->order_num = $order_num;
        }
        foreach($this->data['drinks'] as $meal)
        {
            $meal->order_num = $order_num;
        }
        foreach($this->data['sweets'] as $meal)
        {
            $meal->order_num = $order_num;
        }
        
        $this->render();
    }

    // make a menu ordering column
    function make_column($category) {
        //FIXME
        return $this->menu->some('category', $category);
    }

    // add an item to an order
    function add($order_num, $item) {
        //FIXME
        $this->orders->add_item($order_num, $item);
        
        $this->display_menu($order_num);
    }

    // checkout
    function checkout($order_num) {
        $this->data['title'] = 'Checking Out';
        $this->data['pagebody'] = 'show_order';
        $this->data['order_num'] = $order_num;
        //FIXME
        $this->data['total'] = $this->orders->total($order_num);
        
        $items = $this->orderitems->group($order_num);
        
        foreach($items as $item)
        {
            $menuitem = $this->menu->get($item->item);
            $item->code = $menuitem->name;
        }
        
        $this->data['items'] = $items;
        
        $this->render();
    }

    // proceed with checkout
    function commit($order_num) {
        if(!$this->orders->validate($order_num))
            redirect('/order/display_menu/' . $order_num);
        $record = $this->orders->get($order_num);
        $record->date = date(DATE_ATOM);
        $record->status = 'c';
        $record->total = $this->orders->total($order_num);
        $this->orders->update($record);
        redirect('/');
    }

    // cancel the order
    function cancel($order_num) {
        $this->orderitems->delete_some($order_num);
        $record = $this->orders->get($order_num);
        $record->orders->update($record);
        
        redirect('/');
    }

}
