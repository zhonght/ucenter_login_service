laravel-admin ucenter_login_service
======

### 安装依赖项:

    composer require weigather/ucenter_login_service
     
### 发布资源文件

    php artisan vendor:publish --provider="Weigather\WJUcenterLoginService\WJUcenterLoginServiceServiceProvider"
    
### 执行数据库迁移文件

	php artisan migrate

### 其他
 在 config/wj_ucenter_login_service.php 中设置相关数据 
 
<div>
    <table border="0">
	  <tr>
	    <th>Version</th>
	    <th>Laravel-Admin Version</th>
	  </tr>
	  <tr>
	    <td>^1.0</td>
	    <td>>= 1.6.10</td>
	  </tr>
	  <tr>
	    <td>^2.0</td>
	    <td>>= 1.8.1</td>
	  </tr>
	</table>
</div> 
