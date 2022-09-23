function tabOpen(clicked_id){
  document.querySelector('#'+clicked_id).classList.toggle("active");
  document.querySelector('.'+clicked_id).classList.toggle("open_tab");
}

function closePopup(){
  document.querySelector('.active_pop').classList.toggle("close_pop");
}
