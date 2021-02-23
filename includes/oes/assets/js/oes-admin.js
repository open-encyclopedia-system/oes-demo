/* Make accordion expandable */
initAccordion(document.getElementsByClassName("oes-accordion"), "#e5e5e5", "#f1f1f1")

function initAccordion(acc, backgroundColorHide, backgroundColorBlock) {
    var i;
    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function () {
            this.classList.toggle("active");
            var parent = this.parentElement;
            var panel = parent.nextElementSibling;
            if (panel.style.display === "block") {
                panel.style.display = "none";
                if(acc[0].id !== "info"){
                    //parent.style.backgroundColor = backgroundColorHide;
                }
            } else {
                panel.style.display = "block";
                parent.parentElement.style.backgroundColor = backgroundColorBlock;
                //parent.style.backgroundColor = backgroundColorHide;
            }
        });
    }
}