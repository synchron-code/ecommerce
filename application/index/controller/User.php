<?php
/**
 * Created by PhpStorm.
 * User: guo
 * Date: 11/28/16
 * Time: 3:43 PM
 */
namespace app\index\controller;
use think\Controller;
use think\Db;

class User extends Controller
{
    public function _initialize()
    {
        session_start();
    }

    public function index()
    {
        //session_start();
        if(!isset($_SESSION['customerID']) && !isset($_SESSION['adminID'])){
            $url = str_replace(".html", "", url("Index/index"));
            $url = str_replace(".php", "", $url);
            $url = str_replace("/index", "", $url);
            $this->error("You need to log in first!", $url);
        }
        //session_start();
        if(isset($_SESSION['name']))
        {
            $this->assign('name',$_SESSION['name']);
        }
        if(isset($_SESSION['username']))
        {
            $this->assign('username',$_SESSION['username']);
        }
        if(isset($_SESSION['adminID']))
        {
            $this->assign('adminID',$_SESSION['adminID']);
        }
        if(isset($_SESSION['customerID']))
        {
            $this->assign('customerID',$_SESSION['customerID']);
            $customerID=$_SESSION['customerID'];
            if($customerID%2==1)
            {
                $condition=array('customerID'=>$customerID);
                $home_customer=db("home_customers")->where($condition)->find();
                $this->assign('username',$home_customer['username']);
                $this->assign('name',$home_customer['nick_name']);
                $this->assign('fname',$home_customer['first_name']);
                $this->assign('lname',$home_customer['last_name']);
                $this->assign('income',$home_customer['income']);
                $this->assign('age',$home_customer['age']);
                $this->assign('gender',$home_customer['gender']);
                $this->assign('marriage',$home_customer['marriage_status']);
                $this->assign('email',$home_customer['email']);
            }
            else
            {
                $condition=array('customerID'=>$customerID);
                $business_customer=db("business_customers")->where($condition)->find();
                $this->assign('username',$business_customer['username']);
                $this->assign('name',$business_customer['company_name']);
                $this->assign('annual_income',$business_customer['annual_income']);
                $this->assign('email',$business_customer['email']);
                $this->assign('business_categoryID',$business_customer['business_categoryID']);
                $business_categoryID=$business_customer['business_categoryID'];
                $condition=array('business_categoryID'=>$business_categoryID);
                $business_category=db('business_category')->where($condition)->find();
                $this->assign('business_category_name',$business_category['business_category_name']);
                $business_category = Db::table('business_category')->select();
                $this->assign('business_category', $business_category);
            }
            //Get address list
            $addressList = db("customers_have_address")->where('customerID', $customerID)->select();
            $addressIDs = array();
            foreach ($addressList as $addr) {
                $addressIDs[] = $addr['addressID'];
            }
            if(!empty($addressIDs)) {
                $addresses = db("address")->where("addressID", "in", $addressIDs)->select();
                $this->assign("addresses", $addresses);
            }
            //Get billing info
            $billingList = db("customers_have_billinginfo")->where('customerID', $customerID)->select();
            $billingIDs = array();
            foreach ($billingList as $billing) {
                $billingIDs[] = $billing['billingID'];
            }
            if(!empty($billingIDs)){
                $billinginfos = db("billinginfo")->where("billingID", "in", $billingIDs)->select();
                $this->assign("billinginfos", $billinginfos);
            }
        }
        else
        {
            $this->assign('adminID',$_SESSION['adminID']);
            $adminID=$_SESSION['adminID'];
            $condition=array('adminID'=>$adminID);
            $admin=db('admin')->where($condition)->find();
            $this->assign('username',$admin['username']);
            $this->assign('fname',$admin['first_name']);
            $this->assign('lname',$admin['last_name']);
            $this->assign('email',$admin['email']);
        }
       return $this->fetch();
    }

    public function signin()
    {
        $business_category = Db::table('business_category')->select();
        $this->assign('business_category', $business_category);
        return $this->fetch();
    }
    public function addUser()
    {

        if (isset($_POST['customerType']) && $_POST['customerType'] == "Home") {
            if (isset($_POST["Username"]) && isset($_POST['customerType']) && isset($_POST["Password"]) && isset($_POST["checkPassword"]) && isset($_POST["NickName"]) && isset($_POST["Fname"]) && isset($_POST["Lname"]) && isset($_POST["Income"]) && isset($_POST["Age"])) {
                $username = $_POST["Username"];
                $customerType = $_POST['customerType'];
                $password = $_POST["Password"];
                $checkpassword = $_POST["checkPassword"];
                $nickname = $_POST["NickName"];
                $fname = $_POST["Fname"];
                $lname = $_POST["Lname"];
                $income = $_POST["Income"];
                $age = $_POST["Age"];
                $gender = $_POST["gender"];
                $marriage = $_POST["marriage"];
                $email = $_POST["email"];
                $result = $this->doubleName($username, $customerType);
                if ($result) {
                    $this->error("The username has already existed. Please make a new one");
                } else {
                    if ($password != $checkpassword) {
                        $this->error("The password are not same");
                    } else {

                        if ($age == "" && $income != "") {
                            $data = (['username' => $username, 'password' => $password, 'nick_name' => $nickname, 'first_name' => $fname, 'last_name' => $lname, 'income' => $income, 'gender' => $gender, 'marriage_status' => $marriage, 'email' => $email]);
                        } else if ($income == "" && $age != "") {
                            $data = (['username' => $username, 'password' => $password, 'nick_name' => $nickname, 'first_name' => $fname, 'last_name' => $lname, 'gender' => $gender, 'marriage_status' => $marriage, 'email' => $email, 'age' => $age]);
                        } else if ($income == "" && $age == "") {
                            $data = (['username' => $username, 'password' => $password, 'nick_name' => $nickname, 'first_name' => $fname, 'last_name' => $lname, 'gender' => $gender, 'marriage_status' => $marriage, 'email' => $email]);
                        } else {
                            $data = (['username' => $username, 'password' => $password, 'nick_name' => $nickname, 'first_name' => $fname, 'last_name' => $lname, 'income' => $income, 'age' => $age, 'gender' => $gender, 'marriage_status' => $marriage, 'email' => $email]);

                        }
                        $result = Db::table('home_customers')->insertGetId($data);
                    }

                    if ($result) {
                        $condition = array('username' => $username);
                        $home_customer = db("home_customers")->where($condition)->find();
                        ## session_start();
                        $_SESSION['username'] = $home_customer['username'];
                        $_SESSION['customerID'] = $home_customer['customerID'];
                        $_SESSION['name'] = $home_customer['nick_name'];
                        $url = str_replace(".html", "", url("User/fillAddress"));
                        $this->success('OK!', $url);
                    } else {
                        $this->error("something weird happen");
                    }
                }
            } else {
                $this->error("fatal error!");
            }
        } else if (isset($_POST['customerType']) && $_POST['customerType'] == "Business") {
            if (isset($_POST["Username"]) && isset($_POST['customerType']) && isset($_POST["Password"]) && isset($_POST["checkPassword"]) && isset($_POST["company_name"]) && isset($_POST["annual_income"]) && isset($_POST["category"])) {
                $username = $_POST["Username"];
                $customerType = $_POST['customerType'];
                $password = $_POST["Password"];
                $checkpassword = $_POST["checkPassword"];
                $company_name = $_POST["company_name"];
                $annual_income = $_POST["annual_income"];
                $category = $_POST["category"];
                $email = $_POST["email"];
                $result = $this->doubleName($username, $customerType);
                if ($result) {
                    $this->error("The username has already existed. Please make a new one");
                } else {
                    if ($password != $checkpassword) {
                        $this->error("The password are not same");
                    } else {
                        //$customerID=Db::query("select max(customerID) from business_customers");
                        $customers = Db::table('business_customers')->field("customerID")->select();
                        $customerID = 0;
                        foreach ($customers as $arr) {
                            if (intval($arr['customerID']) > $customerID) {
                                $customerID = intval($arr['customerID']);
                            }
                        }
                        $customerID = (int)$customerID + 2;
                        if ($annual_income == "") {
                            $data = (['customerID' => $customerID, 'username' => $username, 'password' => $password, 'email' => $email, 'company_name' => $company_name, 'business_categoryID' => $category]);
                        } else {
                            $data = (['customerID' => $customerID, 'username' => $username, 'password' => $password, 'email' => $email, 'annual_income' => $annual_income, 'company_name' => $company_name, 'business_categoryID' => $category]);
                        }
                        $result = Db::table('business_customers')->insert($data);
                    }
                    if ($result) {
                        $condition = array('username' => $username);
                        $business_customer = db('business_customers')->where($condition)->find();
                        //session_start();
                        $_SESSION['username'] = $business_customer['username'];
                        $_SESSION['customerID'] = $business_customer['customerID'];
                        $_SESSION['name'] = $business_customer['company_name'];
                        $url = str_replace(".html", "", url("User/fillAddress"));
                        $this->success('OK!', $url);
                    } else {
                        $this->error("something weird happen");
                    }
                }
            } else {
                $this->error("fatal error!");
            }
        } else {
            $this->error("fatal error!");
        }
    }
    public function fillAddress()
    {
        if(!isset($_SESSION['customerID']) && !isset($_SESSION['adminID'])){
            $url = str_replace(".html", "", url("Index/index"));
            $url = str_replace(".php", "", $url);
            $url = str_replace("/index", "", $url);
            $this->error("You need to log in first!", $url);
        }
        if(isset($_GET['type']) && $_GET['type'] == "update"){
            $this->assign("type", "update");
        }
        else if(isset($_GET['type']) && $_GET['type'] == "payment") {
            $this->assign("type", "payment");
        }
        if(isset($_SESSION['name']))
        {
            $this->assign('name',$_SESSION['name']);
        }
        if(isset($_SESSION['username']))
        {
            $this->assign('username',$_SESSION['username']);
        }
        if(isset($_SESSION['adminID']))
        {
            $this->assign('adminID',$_SESSION['adminID']);
        }
        return $this->fetch();
    }
    public function addAddress()
    {
        if(isset($_POST["state"])&&isset($_POST['city'])&&isset($_POST["street"])&&isset($_POST["zipcode"]))
        {
            //session_start();
            $state= $_POST["state"];
            $city= $_POST["city"];
            $street=$_POST['street'];
            $zipcode=$_POST['zipcode'];
            $data=(['state'=>$state,'city'=>$city,'street'=>$street,'zip_code'=>$zipcode]);
            $customerID=$_SESSION['customerID'];
            $addressID=Db::table('address')->insertGetId($data);
            $data=(['customerID'=>$customerID,'addressID'=>$addressID]);
            $result=Db::table('customers_have_address')->insert($data);
            if ($result)
            {
                if(isset($_GET['type']) && $_GET['type'] == "update"){
                    $url = str_replace(".html", "", url("User/index"));
                }
                else if(isset($_GET['type']) && $_GET['type'] == "payment"){
                    $url = str_replace(".html", "", url("Cart/payment"));
                }
                else{
                    $url = str_replace(".html", "", url("User/fillCardInfo"));
                }
                $this->success("adding address successfully",$url);
            }
            else
            {
                $this->error("I am sorry. Your address has some problem.");
            }
        }
        else
        {
            $this->error("fatal error");
        }
    }
    //Only comes after fillAddress step()
    public function fillCardInfo()
    {
        if(!isset($_SESSION['customerID']) && !isset($_SESSION['adminID'])){
            $url = str_replace(".html", "", url("Index/index"));
            $url = str_replace(".php", "", $url);
            $url = str_replace("/index", "", $url);
            $this->error("You need to log in first!", $url);
        }
        if(isset($_GET['type']) && $_GET['type'] == "update"){
            $this->assign("type", "update");
        }
        else if(isset($_GET['type']) && $_GET['type'] == "payment") {
            $this->assign("type", "payment");
        }
        if(isset($_SESSION['name']))
        {
            $this->assign('name',$_SESSION['name']);
        }
        if(isset($_SESSION['username']))
        {
            $this->assign('username',$_SESSION['username']);
        }
        if(isset($_SESSION['adminID']))
        {
            $this->assign('adminID',$_SESSION['adminID']);
        }
        return $this->fetch();
    }
    public function addCard()
    {
        if(isset($_POST["creditcard_number"])&&isset($_POST['year'])&&isset($_POST["month"]))
        {
            //session_start();
            $creditcard_number= $_POST["creditcard_number"];
            $year= $_POST["year"];
            $month=$_POST['month'];
            $customerID=$_SESSION['customerID'];
            $data=(['creditcard_number'=>$creditcard_number,'expire_year'=>$year,'expire_month'=>$month]);
            $billingID=Db::table('billinginfo')->insertGetId($data);
            $data=(['customerID'=>$customerID,'billingID'=>$billingID]);
            $result=Db::table('customers_have_billinginfo')->insert($data);
            if ($result)
            {
                if(isset($_GET['type']) && $_GET['type'] == "update"){
                    $url = str_replace(".html", "", url("User/index"));
                }
                else if(isset($_GET['type']) && $_GET['type'] == "payment"){
                    $url = str_replace(".html", "", url("Cart/payment"));
                }
                else{
                    $url = str_replace(".html", "", url("Index/index"));
                    $url = str_replace("/index", "", $url);
                }
                $this->success("adding billing information successfully",$url);
            }
            else
            {
                $this->error("I am sorry. Your billing has some problem.");
            }
        }
        else
        {
            $this->error("fatal error");
        }
    }
    public function goback()
    {
        $url = str_replace(".html", "", url("Index/index"));
        $url = str_replace("/index", "", $url);
        $this->redirect($url,301);
    }
    public function updateInformation()
    {
        //session_start();
        if(isset($_SESSION['customerID']))
        {
            $customerID=$_SESSION['customerID'];
            if($customerID%2==1)
            {
                $nickname=$_POST["NickName"];
                $fname=$_POST["Fname"];
                $lname=$_POST["Lname"];
                $income=$_POST["Income"];
                $age=$_POST["Age"];
                $gender=$_POST["gender"];
                $marriage=$_POST["marriage"];
                $email=$_POST["email"];
                $data=(['nick_name'=>$nickname,'first_name'=>$fname,'last_name'=>$lname,'income'=>$income,'age'=>$age,'gender'=>$gender,'email'=>$email,'marriage_status'=>$marriage]);
                $condition=array('customerID'=>$customerID);
                $result=db('home_customers')->where($condition)->update($data);
                if ($result)
                {
                    $url = str_replace(".html", "", url("User/index"));
                    $this->success("update successfully",$url);
                }
                else
                {
                    $this->error("something wrong happen");
                }
            }
            else
            {
                $company_name=$_POST["company_name"];
                $annual_income=$_POST["annual_income"];
                $categoryID=$_POST["category"];
                $email=$_POST["email"];
                $data=(['company_name'=>$company_name,'annual_income'=>$annual_income,'email'=>$email,'business_categoryID'=>$categoryID]);
                $condition=array('customerID'=>$customerID);
                $result=db('business_customers')->where($condition)->update($data);
                if($result)
                {
                    $url = str_replace(".html", "", url("User/index"));
                    $this->success("update successfully",$url);
                }
                else
                {
                    $this->error("something wrong happen");
                }
            }
        }
        else
        {
            $adminID=$_SESSION['adminID'];
            $first_name=$_POST["fname"];
            $last_name=$_POST["lname"];
            $email=$_POST["email"];
            $data=(['first_name'=>$first_name,'last_name'=>$last_name,'email'=>$email,]);
            $condition=array('adminID'=>$adminID);
            $result=db('admin')->where($condition)->update($data);
            if($result)
            {
                $url = str_replace(".html", "", url("User/index"));
                $this->success("update successfully",$url);
            }
            else
            {
                $this->error("something wrong happen");
            }
        }

    }

    public function updateBillinginfo(){
        if(isset($_POST['billingID']) && isset($_POST['creditcard_number']) && isset($_POST['year']) && isset($_POST['month'])){
            //return $_POST['billingID']." ".$_POST['creditcard_number']." ".$_POST['year']." ".$_POST['month'];
            $data = array();
            $data['billingID'] = $_POST['billingID'];
            $data['creditcard_number'] = $_POST['creditcard_number'];
            $data['expire_year'] = $_POST['year'];
            $data['expire_month'] = $_POST['month'];
            $customerID = $_SESSION['customerID'];
            Db::table("billinginfo")->update($data);
            if(true){
                $this->success("Update Billing Information Successfully!");
            }
            else{
                $this->error("Something Wrong with Update Billing Information!");
            }
        }
        else{
            return "Make America Great Again!";
        }
    }

    public function updateAddress(){
        if(isset($_POST['addressID']) && isset($_POST['street']) && isset($_POST['city']) && isset($_POST['state']) && isset($_POST['zipcode'])){
            //return $_POST['billingID']." ".$_POST['creditcard_number']." ".$_POST['year']." ".$_POST['month'];
            $data = array();
            $data['addressID'] = $_POST['addressID'];
            $data['street'] = $_POST['street'];
            $data['city'] = $_POST['city'];
            $data['state'] = $_POST['state'];
            $data['zip_code'] = $_POST['zipcode'];
            $customerID = $_SESSION['customerID'];
            Db::table("address")->update($data);
            if(true){
                $this->success("Update Address Information Successfully!");
            }
            else{
                $this->error("Something Wrong with Update Address Information!");
            }
        }
        else{
            return "Make America Great Again!";
        }
    }


    public function doubleName($username,$customerType)
    {
        if ($customerType=="Home")
        {
            $condition=array('username'=>$username);
            $result=db("home_customers")->where($condition)->find();
            if($result)
            {
                return 1;
            }
            else
            {
                return 0;
            }
        }
        else
        {
            $condition=array('username'=>$username);
            $result=db("business_customers")->where($condition)->find();
            if($result)
            {
                return 1;
            }
            else
            {
                return 0;
            }
        }
    }
    public function password()
    {
        if(!isset($_SESSION['customerID']) && !isset($_SESSION['adminID'])){
            $url = str_replace(".html", "", url("Index/index"));
            $url = str_replace(".php", "", $url);
            $url = str_replace("/index", "", $url);
            $this->error("You need to log in first!", $url);
        }
        if(isset($_SESSION['name']))
        {
            $this->assign('name',$_SESSION['name']);
        }
        if(isset($_SESSION['username']))
        {
            $this->assign('username',$_SESSION['username']);
        }
        if(isset($_SESSION['adminID']))
        {
            $this->assign('adminID',$_SESSION['adminID']);
        }
        return $this->fetch();


    }
    public function changePassword()
    {
        //session_start();
        if(isset($_SESSION['customerID']))
        {
            $customerID=$_SESSION['customerID'];
            $old_password=$_POST['old_password'];
            $new_password=$_POST['new_password'];
            $check_password=$_POST['check_password'];
            if($new_password!=$check_password)
            {
                $this->error("passwords are not consistent");
            }
            else
            {
                $condition=array('customerID'=>$customerID);
                $data=(['password'=>$new_password]);
                if($customerID%2==1)
                {
                    $home_customer=db('home_customers')->where($condition)->find();
                    $original_password=$home_customer['password'];
                    if($original_password==$old_password)
                    {
                        $result=db('home_customers')->where($condition)->update($data);
                        if($result)
                        {
                            $url = str_replace(".html", "", url("Index/index"));
                            $url = str_replace("/index", "", $url);
                            $this->success("password has been changed",$url);
                        }
                        else
                        {
                            $this->error("something wrong happen");
                        }
                    }
                    else
                    {
                        $this->error("your old password is incorrect");
                    }
                }
                else
                {
                    $business_customer=db('business_customers')->where($condition)->find();
                    $original_password=$business_customer['password'];
                    if($original_password==$old_password)
                    {
                        $result=db('business_customers')->where($condition)->update($data);
                        if($result)
                        {
                            $url = str_replace(".html", "", url("Index/index"));
                            $url = str_replace("/index", "", $url);
                            $this->success("password has been changed",$url);
                        }
                        else
                        {
                            $this->error("something wrong happen");
                        }
                    }
                    else
                    {
                        $this->error("your old password is incorrect");
                    }
                }
            }

        }
        else
        {
            $adminID=$_SESSION['adminID'];
            $old_password=$_POST['old_password'];
            $new_password=$_POST['new_password'];
            $check_password=$_POST['check_password'];
            if($new_password!=$check_password)
            {
                $this->error("passwords are not consistent");
            }
            else
            {
                $condition=array('adminID'=>$adminID);
                $data=(['password'=>$new_password]);
                $admin=db('admin')->where($condition)->find();
                $original_password=$admin['password'];
                if($original_password==$old_password)
                    {
                        $result=db('admin')->where($condition)->update($data);
                        if($result)
                        {
                            $url = str_replace(".html", "", url("Index/index"));
                            $url = str_replace("/index", "", $url);
                            $this->success("password has been changed",$url);
                        }
                        else
                        {
                            $this->error("something wrong happen");
                        }
                    }
                    else
                    {
                        $this->error("your old password is incorrect");
                    }
                }
        }
    }


    public function checkBusinessUsername()
    {
        $username=$_POST['username'];
        $condition=array('username'=>$username);
        $user=db('business_customers')->where($condition)->find();
        if ($username)
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }

    public function getAddress(){
        if(isset($_SESSION['customerID'])){
            if(isset($_POST['addressID'])){
                $data['addressID'] = $_POST['addressID'];
                $data['customerID'] = $_SESSION['customerID'];
                if(db('customers_have_address')->where($data)->find()){
                    unset($data['customerID']);
                    $address = db('address')->where($data)->find();
                    return json_encode($address);
                }
                else{
                    return null;
                }
            }
            else{
                return "Illegal operation!";
            }
        }
        else{
            return "Illegal operation!";
        }
    }

    public function getBillingInfo(){
        if(isset($_SESSION['customerID'])){
            if(isset($_POST['billingID'])){
                $data['billingID'] = $_POST['billingID'];
                $data['customerID'] = $_SESSION['customerID'];
                if(db('customers_have_billinginfo')->where($data)->find()){
                    unset($data['customerID']);
                    $billinginfo = db('billinginfo')->where($data)->find();
                    return json_encode($billinginfo);
                }
                else{
                    return null;
                }
            }
            else{
                return "Illegal operation!";
            }
        }
        else{
            return "Illegal operation!";
        }
    }
    public function order_check_customer()
    {
        if(isset($_SESSION['name']))
        {
            $this->assign('name',$_SESSION['name']);
        }
        if(isset($_SESSION['username']))
        {
            $this->assign('username',$_SESSION['username']);
        }
        if(isset($_SESSION['adminID']))
        {
            $this->assign('adminID',$_SESSION['adminID']);
        }
        if(isset($_SESSION['customerID']))
        {
            $customerID=$_SESSION['customerID'];
            $orders=Db('products')->alias('p')->join('orders o','o.productID=p.productID')->where('customerID',$customerID)->select();
            if($orders)
            {
                $this->assign('orders',$orders);
                return $this->fetch();
            }
            else
            {
                $this->error("It is empty. Go and buy Now!");
            }
        }
    }
}