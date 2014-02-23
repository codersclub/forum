function cca(cb,style) {
   var d,i;
   while(cb.tagName!='TR') cb=cb.parentNode;
   cb.dark=cb.dark?cb.dark=d=false:cb.dark=d=true;
   cb=cb.cells;
   for(i=0;i<cb.length;i++) cb[i].className=d?(cb[i].oldClass=cb[i].className,style):cb[i].oldClass;
}

 function checkdelete(caption) {
 
   isDelete = document.topic.tact.options[document.topic.tact.selectedIndex].value;
   
   msg = '';
   
   if (isDelete == 'delete')
   {
	   formCheck = confirm(caption);
	   
	   if (formCheck == true)
	   {
		   return true;
	   }
	   else
	   {
		   return false;
	   }
   }
 }

$(document).ready(function(){
    $('.topic-column-mod_checkbox input:checkbox').click(function(){
        $(this).parents('tr').toggleClass('selected');
    });
})
