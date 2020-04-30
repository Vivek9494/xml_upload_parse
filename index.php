<?php
session_start();
include "connection.php";

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>XML Task </title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <?php include('css.php')?>

        <style>
            .error{color:red;}
            .success{color:green;}
        </style>
    </head>
    <body class="hold-transition sidebar-mini">
        <!-- Site wrapper -->
        <div class="wrapper">

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1>XML Product List</h1>
                            </div>
                            
                        </div>
                    </div><!-- /.container-fluid -->
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="card">  
                        <div class="card-body">
                            <form role="form" name="edit_event_frm" id="edit_event_frm" action="upload_xml.php" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Upload XML File</label>
                                            <!-- <label for="customFile">Custom File</label> -->
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="userfile" name="userfile" accept="text/xml">
                                                <label class="custom-file-label" id="filename_text" for="customFile">Choose file</label>
                                            </div>
                                            <?php if(isset($_SESSION['error_message'])){
                                                    echo '<p class="error">'.$_SESSION['error_message'].'<p>';
                                                    unset($_SESSION['error_message']);
                                            }?>
                                            <?php if(isset($_SESSION['success_message'])){
                                                    echo '<p class="success">'.$_SESSION['success_message'].'<p>';
                                                    unset($_SESSION['success_message']);
                                            }?>
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="margin-top: 1.75rem;">
                                        <input type="submit" name="upload_btn" id="upload_btn" class="btn btn-primary" value="Upload File" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Default box -->
                    <div class="card">
                        <div class="card-body">
                            <?php 
                            $product_sql = "SELECT * FROM xml_products";
                            $product_result = mysqli_query($conn,$product_sql);
                            $product_rows = mysqli_num_rows($product_result);

                            if(!empty($product_rows)){
                                while($product_row = mysqli_fetch_assoc($product_result)){
                                    echo '<h2>'.$product_row['product_name'].'</h2>';

                                    $select_category_sql = 'SELECT * FROM xml_product_categories WHERE product_id="'.$product_row['id'].'"';
                                    $category_result = mysqli_query($conn,$select_category_sql);
                                    $category_rows = mysqli_num_rows($category_result);
                                    
                                    if(!empty($category_rows)){ ?>
                                    <div class="row">
                                        <?php while($category_row = mysqli_fetch_assoc($category_result)){ ?>
                                            <div class="col-md-6">
                                                <h4><?php echo $category_row['category_name'];?></h4>
                                                <table id="<?php echo $category_row['id'];?>" class="table table-bordered table-striped category_table">
                                                    <thead>
                                                        <tr>
                                                            <th>Item</th>
                                                            <th>Price</th>
                                                            <th>Size - Color</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                            $select_item_sql = 'SELECT * FROM xml_product_items WHERE category_id="'.$category_row['id'].'"';
                                                            $item_result = mysqli_query($conn,$select_item_sql);
                                                            $item_rows = mysqli_num_rows($item_result);

                                                            if(!empty($item_rows)){
                                                        
                                                                while($item_row = mysqli_fetch_assoc($item_result)){
                                                                    $select_size_color_sql = 'SELECT * FROM xml_item_size_colors WHERE item_id="'.$item_row['id'].'"';
                                                                    $size_color_result = mysqli_query($conn,$select_size_color_sql);
                                                                    $size_color_rows = mysqli_num_rows($size_color_result); ?>

                                                                    <tr>
                                                                        <td><?php echo $item_row['item_code'];?></td>
                                                                        <td><?php echo $item_row['item_price'];?></td>
                                                                        <td>
                                                                        <?php if(!empty($size_color_rows)){
                                                                                echo '<ul>';
                                                                                while($size_color_row = mysqli_fetch_assoc($size_color_result)){ ?>
                                                                                    <li><?php echo $size_color_row['size'].' - '.$size_color_row['color']; ?></li>
                                                                                <?php }
                                                                            }?>
                                                                        </td>                                                                        
                                                                    </tr>
                                                        <?php   }
                                                            } ?>
                                                    </tbody>
                                                </table> 
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <?php } 
                                }
                            }?>
                        </div>
                    </div>
                    <!-- /.card -->
                </section>
            </div>
            <!-- /.content-wrapper -->

            <?php include('footer.php');?>
        </div>
        <!-- ./wrapper -->
        <?php include('js.php');?>

        <script>
            $(function () {
                $(".category_table").DataTable({
                    "paging": true,
                    "lengthChange": false,
                    "pageLength": 5,
                    "searching": false,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                });

                $('input[type="file"]'). change(function(e){
                    var fileName = e. target. files[0]. name;
                    $('#filename_text').text(fileName);
                });
            });
        </script>
    </body>
</html>