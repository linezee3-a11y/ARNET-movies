const up1 = document.getElementById('up1');
const up2 = document.getElementById('up2');
const up3 = document.getElementById('up3');
const c1 = document.getElementById('c1');
const c2 = document.getElementById('c2');
const p1 = document.getElementById('p1');
const p2 = document.getElementById('p2');
const video = document.getElementById('video');
const n1 = document.getElementById('n1');
const n2 = document.getElementById('n2');
const n3 = document.getElementById('n3');
const uploadbtn = document.getElementById('upb');
const uploader = document.getElementById('uploader');
const loader2 = document.querySelector('.load2');
const uploadContainer = document.querySelector('.uploads');
const uploadok = document.querySelector('.ok');
const copy = document.getElementById('copy');
const settingbtn = document.getElementById('setti');    
const cancelsettingbtn = document.getElementById('can');    
const settingcont = document.querySelector('.setting');
const settingconts = document.querySelector('.settings');



cancelsettingbtn.addEventListener('click',()=>{
    settingconts.style.display = 'none';
})

settingbtn.addEventListener('click',()=>{
    settingconts.style.display = 'flex';
    setTimeout(() => {
        loader2.style.display = 'none';
        settingcont.style.height = '80vh';
    }, 3000);
})



copy.addEventListener('click', () =>{
    copy.textContent = 'copied';
    copy.style.backgroundColor = 'green';
    
    setTimeout(() => {
        copy.textContent = 'copy link';
        copy.style.backgroundColor = 'rgb(230, 54, 0)';
    }, 3000);
})



n1.addEventListener('click',() =>{
    up1.style.display = 'none';
    up2.style.display = 'flex';
});
n3.addEventListener('click',() =>{
    uploader.style.display = 'flex';
        setTimeout(() => {
        uploader.style.display = 'none';
        uploadok.style.display = 'flex';
        uploadContainer.style.display = 'none';
    }, 3000);
    setTimeout(() => {
    uploadok.style.transform = 'translateY(300%)';
    uploadok.style.display = 'none';
}, 5000);

});

c1.addEventListener('click',() =>{
    uploadContainer.style.display = 'none';
});
uploadbtn.addEventListener('click',() =>{
    uploadContainer.style.display = 'flex';
    setTimeout(() => {
        uploader.style.display = 'none';
    }, 3000);
    
});
c2.addEventListener('click',() =>{
    up1.style.display = 'flex';
    up2.style.display = 'none';
});
n2.addEventListener('click',() =>{
    up3.style.display = 'block';
    up2.style.display = 'none';
    up1.style.display = 'none';
});
c2.addEventListener('click',() =>{
    up1.style.display = 'block';
    up3.style.display = 'none';
    up2.style.display = 'none';
});
c3.addEventListener('click',() =>{
    up1.style.display = 'none';
    up3.style.display = 'none';
    up2.style.display = 'flex';
});
