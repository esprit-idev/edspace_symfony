

const v=document.getElementById("button");
if(v){
v.addEventListener('click', event=>{
  document.querySelector('.bg-modal').style.display='flex';
  document.querySelector('.modal-content').style.display='flex';
})
}


document.querySelector('.close').addEventListener('click',
function(){
  document.querySelector('.bg-modal').style.display='none';
})


const w=document.querySelector('.close1')
if(w){
w.addEventListener('click',
function(){
  document.querySelector('.bg-modal').style.display='none';
})


const divs = document.querySelectorAll('.a-item');



divs.forEach(el => el.addEventListener('click', event => {

  const fid=document.querySelector('.id');
fid.value=el.querySelector('.id').textContent;

  const fniveau=document.querySelector('.niveau');
fniveau.value=el.querySelector('.niveau').textContent;
fniveau.textContent=el.querySelector('.niveau').textContent;

const fclasse=document.querySelector('.classe');
fclasse.value=el.querySelector('.classe').textContent;


}));
const x=document.querySelectorAll('.c-item');
x.forEach(e => e.addEventListener('click', event=>{
  document.querySelector('.bg-modal').style.display='flex';
  document.querySelector('.modal-content').style.display='flex';
  document.querySelector('.modal-content1').style.display='none';
}))








document.getElementById('19').addEventListener('click', event=>{
  document.querySelector('.bg-modal').style.display='flex';
  document.querySelector('.modal-content1').style.display='flex';
  document.querySelector('.modal-content').style.display='none';
})

const form=document.getElementById('form');


const classe=document.getElementById('Classe');
form.addEventListener('submit',e=>{
  checkInputs(e);

})

function checkInputs(e){
  const classeValue=classe.value.trim();

  if(classeValue === ''){
    e.preventDefault();
    setErrorFor(classe,'Classe ne peux pas être vide');
  }
  else{
    if(!Number.isInteger(Number(classeValue))){
      e.preventDefault();
      setErrorFor(classe,'doit être un nombre');
    }
    else {
    setSuccessFor(classe);
    }
  }


}
function setErrorFor(input, message){
  const div=input.parentElement;
  const small=div.querySelector('small');
  small.innerHTML= message;
  div.className='col-md-12 error';
}

function setSuccessFor(input){
  const div=input.parentElement;
  div.className='col-md-12 success';
}

const form2=document.getElementById('form2');

const class2=document.getElementById('Classe2');
form2.addEventListener('submit',e=>{
  checkInputs2(e);

})

function checkInputs2(e){
  const classeValue2=class2.value.trim();

  if(classeValue2 === ''){
    e.preventDefault();
    setErrorFor(class2,'Classe ne peux pas être vide');
  }
  else{
    if(!Number.isInteger(Number(classeValue2))){
      e.preventDefault();
      setErrorFor(class2,'doit être un nombre');
    }
    else {
      const niveau=document.getElementById('niveau2');
      checkNiveau(class2,niveau,e)
    }
  }


}


function checkNiveau(class2,niveau,e){
if(niveau.value==''){
  e.preventDefault();
  const div2=niveau.parentElement;
  const small=div2.querySelector('small');
  small.innerHTML= "Il faut choisir le niveau";
  div2.className='col-md-12 error';
}
else{
  setSuccessFor(class2);
}

}}