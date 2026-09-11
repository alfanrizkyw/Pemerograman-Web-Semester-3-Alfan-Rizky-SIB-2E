/* SIMPUS-Mini - Versi diperbaiki
   - Data buku/anggota dipindahkan ke localStorage sebagai sumber data utama.
   - Tambah, edit, hapus langsung tersimpan dan otomatis dipakai statistik.
   - Sinkronisasi antar-tab melalui event storage.
   - Statistik dihitung dari data yang sama dengan tabel.
   - Login demo: alfan@gmail.com / alfan
*/

const AUTH_KEY = "spm_user";
const DB = {
  buku: "spm_buku",
  anggota: "spm_anggota",
  peminjaman: "spm_peminjaman"
};
const DEMO_ACCOUNTS = { "alfan@gmail.com": "alfan" };

const DEFAULT_BUKU = [
  {id:1,judul:"Belajar PHP untuk Pemula",pengarang:"Andi Wijaya",tahun:2021,stok:4,kategori:"pelajaran",isbn:""},
  {id:2,judul:"Dasar-Dasar PostgreSQL",pengarang:"Siti Rahma",tahun:2020,stok:2,kategori:"referensi",isbn:""},
  {id:3,judul:"Pemrograman Web Modern",pengarang:"Budi Santoso",tahun:2022,stok:6,kategori:"non-fiksi",isbn:""},
  {id:4,judul:"JavaScript untuk Web",pengarang:"Dewi Lestari",tahun:2019,stok:0,kategori:"pelajaran",isbn:""},
  {id:5,judul:"HTML5 & CSS3 Praktis",pengarang:"Rudi Hartono",tahun:2023,stok:8,kategori:"pelajaran",isbn:""},
  {id:6,judul:"Algoritma & Struktur Data",pengarang:"Agus Salim",tahun:2018,stok:3,kategori:"pelajaran",isbn:""},
  {id:7,judul:"Basis Data Relasional",pengarang:"Nina Kurnia",tahun:2021,stok:5,kategori:"referensi",isbn:""},
  {id:8,judul:"Keamanan Aplikasi Web",pengarang:"Fajar Nugraha",tahun:2022,stok:1,kategori:"non-fiksi",isbn:""},
  {id:9,judul:"Responsive Web Design",pengarang:"Lina Marlina",tahun:2020,stok:7,kategori:"referensi",isbn:""},
  {id:10,judul:"Pengantar Rekayasa Perangkat Lunak",pengarang:"Hendra Gunawan",tahun:2017,stok:2,kategori:"pelajaran",isbn:""}
];
const DEFAULT_ANGGOTA = [
  {id:1,nama:"Ahmad Fauzi",no_anggota:"A001",alamat:"Jl. Merdeka No. 1, Malang",no_hp:"081234567801"},
  {id:2,nama:"Putri Ayu",no_anggota:"A002",alamat:"Jl. Sudirman No. 12, Malang",no_hp:"081234567802"},
  {id:3,nama:"Bayu Pratama",no_anggota:"A003",alamat:"Jl. Ijen No. 5, Malang",no_hp:"081234567803"},
  {id:4,nama:"Citra Dewi",no_anggota:"A004",alamat:"Jl. Kawi No. 8, Malang",no_hp:"081234567804"},
  {id:5,nama:"Doni Setiawan",no_anggota:"A005",alamat:"Jl. Semeru No. 3, Malang",no_hp:"081234567805"}
];
const DEFAULT_PEMINJAMAN = [
  {id:1,buku_id:2,anggota_id:1,tanggal_pinjam:"2026-08-20",status:"dipinjam"},
  {id:2,buku_id:4,anggota_id:3,tanggal_pinjam:"2026-08-25",status:"dipinjam"},
  {id:3,buku_id:8,anggota_id:2,tanggal_pinjam:"2026-09-01",status:"dipinjam"},
  {id:4,buku_id:1,anggota_id:5,tanggal_pinjam:"2026-08-10",status:"dikembalikan"}
];

document.addEventListener("DOMContentLoaded", () => {
  seedDatabase();
  initHamburgerMenu();
  initFormValidasiBuku();
  initFormValidasiAnggota();
  initFilterBuku();
  initFilterAnggota();
  initHapusBaris();
  initFormLogin();
  initAuthNav();
  renderAll();
});

window.addEventListener("storage", (e) => {
  if ([DB.buku, DB.anggota, DB.peminjaman].includes(e.key)) renderAll();
});

function seedDatabase() {
  // Hanya isi sekali. Setelah itu localStorage menjadi sumber data utama.
  if (!localStorage.getItem(DB.buku)) localStorage.setItem(DB.buku, JSON.stringify(DEFAULT_BUKU));
  if (!localStorage.getItem(DB.anggota)) localStorage.setItem(DB.anggota, JSON.stringify(DEFAULT_ANGGOTA));
  if (!localStorage.getItem(DB.peminjaman)) localStorage.setItem(DB.peminjaman, JSON.stringify(DEFAULT_PEMINJAMAN));
}

function getData(key) {
  try {
    const data = JSON.parse(localStorage.getItem(key) || "[]");
    return Array.isArray(data) ? data : [];
  } catch { return []; }
}
function saveData(key, data) {
  localStorage.setItem(key, JSON.stringify(data));
}
function nextId(data) {
  return data.reduce((m,x) => Math.max(m, Number(x.id)||0), 0) + 1;
}
function setFlash(message) {
  sessionStorage.setItem("spm_flash", message);
}
function showFlashIfAny(id) {
  const el = document.getElementById(id), msg = sessionStorage.getItem("spm_flash");
  if (el && msg) {
    setStatus(el, msg, "loading");
    sessionStorage.removeItem("spm_flash");
  }
}

function initHamburgerMenu() {
  const toggle=document.getElementById("navToggle"), menu=document.getElementById("navMenu");
  if(toggle && menu) toggle.addEventListener("click",()=>menu.classList.toggle("open"));
}

function initFormValidasiBuku() {
  const form=document.getElementById("formTambahBuku");
  if(!form) return;
  form.addEventListener("submit",(e)=>{
    e.preventDefault(); clearErrors(form);
    const judul=form.judul.value.trim(), pengarang=form.pengarang.value.trim();
    const tahun=Number(form.tahun.value), stok=Number(form.stok.value);
    const kategori=form.kategori.value, isbn=(form.isbn?.value||"").trim();
    let ok=true, now=new Date().getFullYear();
    if(!judul){showError(form.judul,"Judul wajib diisi.");ok=false}
    if(!pengarang){showError(form.pengarang,"Pengarang wajib diisi.");ok=false}
    if(!Number.isInteger(tahun)||tahun<1900||tahun>now){showError(form.tahun,`Tahun harus antara 1900 - ${now}.`);ok=false}
    if(!Number.isInteger(stok)||stok<0){showError(form.stok,"Stok harus angka bulat >= 0.");ok=false}
    if(!kategori){showError(form.kategori,"Kategori wajib dipilih.");ok=false}
    if(!ok)return;
    const data=getData(DB.buku);
    data.push({id:nextId(data),judul,pengarang,tahun,stok,kategori,isbn});
    saveData(DB.buku,data);
    setFlash(`Buku "${judul}" berhasil ditambahkan.`);
    location.href="list.html";
  });
}

function initFormValidasiAnggota() {
  const form=document.getElementById("formTambahAnggota");
  if(!form) return;
  form.addEventListener("submit",(e)=>{
    e.preventDefault(); clearErrors(form);
    const nama=form.nama.value.trim(), no=form.no_anggota.value.trim();
    const alamat=form.alamat.value.trim(), hp=form.no_hp.value.trim();
    let ok=true;
    if(!nama){showError(form.nama,"Nama wajib diisi.");ok=false}
    if(!no){showError(form.no_anggota,"Nomor anggota wajib diisi.");ok=false}
    if(!alamat){showError(form.alamat,"Alamat wajib diisi.");ok=false}
    if(!/^[0-9+]{9,15}$/.test(hp)){showError(form.no_hp,"Nomor HP tidak valid (9-15 digit).");ok=false}
    if(!ok)return;
    const data=getData(DB.anggota);
    data.push({id:nextId(data),nama,no_anggota:no,alamat,no_hp:hp});
    saveData(DB.anggota,data);
    setFlash(`Anggota "${nama}" berhasil ditambahkan.`);
    location.href="list.html";
  });
}

function showError(input,message){
  const s=document.createElement("span"); s.className="error"; s.textContent=message;
  input.insertAdjacentElement("afterend",s); input.style.borderColor="#dc2626";
}
function clearErrors(form){
  form.querySelectorAll(".error").forEach(x=>x.remove());
  form.querySelectorAll("input,select").forEach(x=>x.style.borderColor="");
}

function initFormLogin(){
  const form=document.getElementById("formLogin"); if(!form)return;
  form.addEventListener("submit",(e)=>{
    e.preventDefault();
    const msg=document.getElementById("loginMsg");
    const email=form.username.value.trim().toLowerCase(), pass=form.password.value;
    if(!email||!pass){msg.innerHTML='<div class="status-msg error">Email dan password wajib diisi.</div>';return}
    if(DEMO_ACCOUNTS[email]===pass){
      sessionStorage.setItem(AUTH_KEY,email);
      msg.innerHTML='<div class="status-msg loading">Login berhasil, mengalihkan...</div>';
      setTimeout(()=>location.href="../index.html",300);
    }else msg.innerHTML='<div class="status-msg error">Email atau password salah.</div>';
  });
}
function initAuthNav(){
  const nav=document.getElementById("navAuthItem"); if(!nav)return;
  const user=sessionStorage.getItem(AUTH_KEY);
  if(user){
    nav.innerHTML=`<span>Halo, ${escapeHtml(user)}</span> | <a href="#" id="logoutLink">Logout</a>`;
    document.getElementById("logoutLink").onclick=(e)=>{e.preventDefault();sessionStorage.removeItem(AUTH_KEY);location.reload()};
  }
}

function initFilterBuku(){
  const input=document.getElementById("searchBuku"); if(!input)return;
  input.addEventListener("input",()=>filterRows("#tbodyBuku",input.value));
}
function initFilterAnggota(){
  const input=document.getElementById("searchAnggota"); if(!input)return;
  input.addEventListener("input",()=>filterRows("#tbodyAnggota",input.value));
}
function filterRows(selector,keyword){
  keyword=keyword.toLowerCase();
  document.querySelectorAll(selector+" tr").forEach(row=>{
    row.style.display=row.textContent.toLowerCase().includes(keyword)?"":"none";
  });
}

function initHapusBaris(){
  document.querySelectorAll("tbody").forEach(tbody=>{
    tbody.addEventListener("click",(e)=>{
      const btn=e.target.closest(".btn-hapus"); if(!btn)return;
      const id=Number(btn.dataset.id), key=btn.dataset.store;
      if(!confirm("Yakin ingin menghapus data ini?"))return;
      const data=getData(key);
      saveData(key,data.filter(x=>Number(x.id)!==id));
      renderAll();
    });
  });
}

function renderAll(){
  renderBuku();
  renderAnggota();
  renderStats();
  showFlashIfAny("statusBuku");
  showFlashIfAny("statusAnggota");
}

function renderBuku(){
  const tbody=document.getElementById("tbodyBuku"); if(!tbody)return;
  const data=getData(DB.buku); tbody.innerHTML="";
  if(!data.length){tbody.innerHTML='<tr><td colspan="5">Belum ada data buku.</td></tr>';return}
  data.forEach(b=>{
    const tr=document.createElement("tr"); tr.dataset.id=b.id;
    tr.innerHTML=`<td>${escapeHtml(b.judul)}</td><td>${escapeHtml(b.pengarang)}</td><td>${b.tahun}</td><td>${b.stok}</td>
    <td><button class="btn btn-edit" type="button" data-id="${b.id}">Edit</button>
    <button class="btn btn-danger btn-hapus" type="button" data-id="${b.id}" data-store="${DB.buku}">Hapus</button></td>`;
    tbody.appendChild(tr);
  });
  tbody.querySelectorAll(".btn-edit").forEach(btn=>btn.onclick=()=>editBuku(Number(btn.dataset.id)));
}
function renderAnggota(){
  const tbody=document.getElementById("tbodyAnggota"); if(!tbody)return;
  const data=getData(DB.anggota); tbody.innerHTML="";
  if(!data.length){tbody.innerHTML='<tr><td colspan="5">Belum ada data anggota.</td></tr>';return}
  data.forEach(a=>{
    const tr=document.createElement("tr"); tr.dataset.id=a.id;
    tr.innerHTML=`<td>${escapeHtml(a.nama)}</td><td>${escapeHtml(a.no_anggota)}</td><td>${escapeHtml(a.alamat)}</td><td>${escapeHtml(a.no_hp)}</td>
    <td><button class="btn btn-edit" type="button" data-id="${a.id}">Edit</button>
    <button class="btn btn-danger btn-hapus" type="button" data-id="${a.id}" data-store="${DB.anggota}">Hapus</button></td>`;
    tbody.appendChild(tr);
  });
  tbody.querySelectorAll(".btn-edit").forEach(btn=>btn.onclick=()=>editAnggota(Number(btn.dataset.id)));
}

function editBuku(id){
  const data=getData(DB.buku), b=data.find(x=>Number(x.id)===id); if(!b)return;
  const judul=prompt("Judul buku:",b.judul); if(judul===null)return;
  const pengarang=prompt("Pengarang:",b.pengarang); if(pengarang===null)return;
  const tahun=prompt("Tahun:",b.tahun); if(tahun===null)return;
  const stok=prompt("Stok:",b.stok); if(stok===null)return;
  if(!judul.trim()||!pengarang.trim()||!Number.isInteger(Number(tahun))||Number(stok)<0){alert("Data edit tidak valid.");return}
  Object.assign(b,{judul:judul.trim(),pengarang:pengarang.trim(),tahun:Number(tahun),stok:Number(stok)});
  saveData(DB.buku,data); renderAll();
}
function editAnggota(id){
  const data=getData(DB.anggota), a=data.find(x=>Number(x.id)===id); if(!a)return;
  const nama=prompt("Nama:",a.nama); if(nama===null)return;
  const no=prompt("No. Anggota:",a.no_anggota); if(no===null)return;
  const alamat=prompt("Alamat:",a.alamat); if(alamat===null)return;
  const hp=prompt("No. HP:",a.no_hp); if(hp===null)return;
  if(!nama.trim()||!no.trim()||!alamat.trim()||!/^[0-9+]{9,15}$/.test(hp)){alert("Data edit tidak valid.");return}
  Object.assign(a,{nama:nama.trim(),no_anggota:no.trim(),alamat:alamat.trim(),no_hp:hp});
  saveData(DB.anggota,data); renderAll();
}

function renderStats(){
  const buku=document.getElementById("statBuku"), anggota=document.getElementById("statAnggota"), pinjam=document.getElementById("statDipinjam");
  if(buku)buku.textContent=getData(DB.buku).length;
  if(anggota)anggota.textContent=getData(DB.anggota).length;
  if(pinjam)pinjam.textContent=getData(DB.peminjaman).filter(x=>x.status==="dipinjam").length;
}

function setStatus(el,message,type){
  if(!el)return;
  el.innerHTML=message?`<div class="status-msg ${type}">${escapeHtml(message)}</div>`:"";
}
function escapeHtml(str){
  const d=document.createElement("div"); d.textContent=String(str); return d.innerHTML;
}
