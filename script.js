function updateTime(){

    let now=new Date();

    let time=
    now.toLocaleTimeString([],{
        hour:"2-digit",
        minute:"2-digit"
    });


    document.getElementById("time").innerHTML=time;
    document.getElementById("clock").innerHTML=time;


    document.getElementById("date").innerHTML=
    now.toLocaleDateString(
        "en-US",
        {
            weekday:"long",
            month:"long",
            day:"numeric"
        }
    );

}


setInterval(updateTime,1000);

updateTime();



function openApp(name){

    document.getElementById("appTitle")
    .innerHTML=name;


    document
    .getElementById("window")
    .classList.add("active");

}



function closeApp(){

    document
    .getElementById("window")
    .classList.remove("active");

}



// phone unlock animation

document.querySelector(".gesture")
.addEventListener("click",()=>{

    document.querySelector(".lockscreen")
    .style.transform="translateY(-300px)";

});