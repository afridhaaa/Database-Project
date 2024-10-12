*{
    padding: 0;
    margin: 0;
}

body{
    font-family: Arial, Helvetica, sans-serif;
}

nav{
    width: 100%;
    background: rgba(135, 207, 235, 0.639);
}

li a{
    text-decoration: none;
    color: grey;
}

li a:hover{
    color: black;
    font-weight: bold;
    transform: 0.5s ease;
}

#logo{
    color: black;
    font-weight: bold;
}

.menu-icon {
    display: none; /* Hidden by default */
    font-size: 30px;
    color: #fff;
    cursor: pointer;
  }

  #nav-links1{
    display: flex;
    justify-content: space-between;
    align-items: center;
    list-style-type: none;
    padding: 20px 0px;
    width: 100%;
  }

  #nav-links1 li{
    padding-left: 30px;
  }

  .logout{
    display: flex;
    gap: 20px;
    padding-right: 30px;
  }

 ul .logout a{
    text-decoration: none;
    color: red;
  }


/* hero-styling  */

.container{
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    justify-content: space-between;
    align-items: center;
    padding: 80px;
    gap: 25px;
}

.box{
    border: 1px solid grey;
    border-radius: 7px;
}

.box-title{
    text-align: center;
    border: 1px solid grey;
    border-radius: 7px 7px 0px 0px;
    padding: 5px;
    background: rgba(172, 255, 47, 0.279);
}

.box-content{
    padding: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
th {
    background-color: #f8f8f8;
    font-weight: bold;
}
tr:hover {
    background-color: #f1f1f1;
}
.table-container {
    width: 100%;
    height: 400px; /* Set height to show only 10 rows and make the rest scrollable */
    overflow-y: auto; /* Enable vertical scroll */
    border: 1px solid #ddd;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 7px;
}

/* Form Container Styling */
.form-container {
    width: 96%;
    padding: 20px;
    background-color: #f5f5f5;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

form{
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

/* Input Fields Styling */
.form-container input {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    background-color: #fff;
    box-sizing: border-box;
}

.form-container input:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Button Styling */
.form-container button {
    width: 100%;
    padding: 12px;
    background-color: #007bff;
    border: none;
    color: white;
    font-size: 16px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.form-container button:hover {
    background-color: #0056b3;
}

/* Title Styling */
.form-container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

.admin-heading{
    text-align: center;
    padding-top: 20px;
}





@media (max-width: 600px){

    body{
        margin: 0px auto;
    }

    .admin-heading{
        text-align: left;
        padding-left: 20px;
    }

    nav{
        width: 164%;
        background: rgba(135, 207, 235, 0.639);
    }

    #nav-links {
        display: none;
    }

    ul{
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        list-style-type: none;
        padding: 0px;
        padding-bottom: 5px;
        width: 100%;
    }

    #logo{
        display: flex;
    }

    .menu-icon {
        display: none;
    
      }

      .menu-icon h1{
        font-size: 1rem;
      }

      .container{
        display: flex;
        flex-direction: column;
        padding: 20px;
        width: fit-content;
      }

}

#nav-links.active {
    display: flex;
  }