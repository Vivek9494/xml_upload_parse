<?php
session_start();
include "connection.php";

echo '<pre>';print_r($_FILES);
$file = $_FILES['userfile'];
$extention = pathinfo($file['name'], PATHINFO_EXTENSION);

if($extention != 'xml'){
    $_SESSION['error_message'] = 'File format is not xml.';
    header('Location:index.php');
}else{
    if(!move_uploaded_file($file['tmp_name'],"xml/".$file['name'])){
        $_SESSION['error_message'] = "There was a problem uploading your file. Please try again.";
        header('Location:index.php');
    }else{
        $_SESSION['success_message'] = "File uploaded successfully";
    }
}

$filename = $_FILES['userfile']['name'];
$file_data = file_get_contents('xml/'.$filename);

$obj = new SimpleXMLElement($file_data);
$XMLarr = json_decode(json_encode($obj), TRUE);

if(is_array($XMLarr) && !empty($XMLarr)){
    $product_name = $XMLarr['product']['@attributes']['description'];
    $product_img = $XMLarr['product']['@attributes']['product_image'];
    
    $select_product_sql = "SELECT * FROM xml_products WHERE product_name='$product_name'";
    $product_result = mysqli_query($conn,$select_product_sql);
    $product_rows = mysqli_num_rows($product_result);
    
    if(empty($product_rows)){
        $insert_sql = 'INSERT INTO xml_products (product_name,product_img) VALUES ("'.$product_name.'","'.$product_img.'")';
        $result = mysqli_query($conn,$insert_sql);
        $product_id = mysqli_insert_id($conn);
    }else{
        $product_row = mysqli_fetch_array($product_result);
        $product_id = $product_row['id'];
    }
    /*--------------------------------------------------------*/
    
    if(!empty($XMLarr['product']['catalog_item'])){
        foreach($XMLarr['product']['catalog_item'] as $index => $item){
            $category = $item['@attributes']['gender'];
        
            $select_category_sql = 'SELECT * FROM xml_product_categories WHERE category_name="'.$category.'" AND product_id="'.$product_id.'"';
            $category_result = mysqli_query($conn,$select_category_sql);
            $category_rows = mysqli_num_rows($category_result);
            
            if(empty($category_rows)){
                $insert_category_sql = 'INSERT INTO xml_product_categories (product_id,category_name) VALUES ("'.$product_id.'","'.$category.'")';
                $result = mysqli_query($conn,$insert_category_sql);
                $id = mysqli_insert_id($conn);
            }else{
                $category_row = mysqli_fetch_array($category_result);
                $id = $category_row['id'];
            }

            $item_list = array_unique($item['item_number']);
            $unique_size_color_array = array_map("unserialize", array_unique(array_map("serialize", $item['size'])));
            
            foreach($item_list as $index2 => $value){
                $price = $item['price'][$index2];

                $select_item_sql = "SELECT * FROM xml_product_items WHERE category_id='$id' AND item_code='$value' AND item_price='$price'";
                $item_result = mysqli_query($conn,$select_item_sql);
                $item_rows = mysqli_num_rows($item_result);
                
                if(empty($item_rows)){
                    $insert_item_sql = "INSERT INTO xml_product_items (category_id,item_code,item_price) VALUES ('$id','$value','$price')";
                    $item_result = mysqli_query($conn,$insert_item_sql);
                    $item_id = mysqli_insert_id($conn);
                }else{
                    $item_row = mysqli_fetch_array($item_result);
                    $item_id = $item_row['id'];
                }
                
                if(!empty($unique_size_color_array)){
                    foreach($unique_size_color_array as $index3 => $value3){
                        $size = $value3['@attributes']['description'];
                        $color = implode(',',$value3['color']);
                        
                        $select_size_color_sql = "SELECT * FROM xml_item_size_colors WHERE item_id='$item_id' AND size='$size' AND color='$color'";
                        $size_color_result = mysqli_query($conn,$select_size_color_sql);
                        $size_color_rows = mysqli_num_rows($size_color_result);

                        if(empty($size_color_rows)){
                            $size_color_sql = "INSERT INTO xml_item_size_colors (item_id,size,color) VALUES ('$item_id','$size','$color')";
                            $sizecolor_result = mysqli_query($conn,$size_color_sql);
                        }                        
                    }
                }
            }
        }
    }
}

header('Location:index.php');
