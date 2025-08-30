# integrated-campus-health-management-system
Integrated Campus Health Management System (ICHMS) – A role-based web application built with PHP, MySQL, HTML, and CSS to manage students, doctors, pharmacists, and admins for campus healthcare. Features include appointments, prescriptions, and drug inventory management.
# Integrated Campus Health Management System (ICHMS)

The **Integrated Campus Health Management System (ICHMS)** is a web-based role-based CRUD application designed to streamline healthcare services within a university campus. It allows students (patients), doctors, pharmacists, and admins to efficiently manage medical records, appointments, prescriptions, and drug inventory.

---

## 🚀 Features

### 👨‍🎓 Student (Patient)
- Register and update personal details  
- Book appointments with available doctors  
- View prescriptions and medical history  
- Check available drugs  

### 👨‍⚕️ Doctor
- Manage availability (schedule)  
- Accept, cancel, or reschedule appointments  
- Issue prescriptions (multiple drugs per appointment)  
- Access patient history  

### 💊 Pharmacist
- Manage drug inventory (stock & expiry dates)  
- View prescriptions issued by doctors  
- Add, update, and remove drugs from stock  

### 🛠️ Admin
- Manage all users (students, doctors, pharmacists)  
- Monitor appointments, prescriptions, and drug stock  
- Access system statistics and dashboards  

---

## 🗄️ Database Design
- **Users** – stores login credentials & roles  
- **Students, Doctors, Pharmacists** – role-specific details  
- **Drugs** – drug stock, expiry, and details  
- **Appointments** – booking details between students & doctors  
- **Prescriptions_Records** – issued prescriptions  

The system supports full **CRUD operations** across entities.

---

## 🛠️ Tech Stack
- **Backend:** PHP  
- **Frontend:** HTML, CSS  
- **Database:** MySQL  
- **Server Environment:** XAMPP  
- **Diagram Tool:** Draw.io  

---

## 🔮 Future Enhancements
- Online billing & payment system  
- SMS/Email reminders for appointments & refills  
- Analytics dashboard for health statistics  
- Mobile app for easier access  

---

## 📚 References
- [Yaho Baba PHP CRUD tutorials](https://www.youtube.com/watch?v=JtG3bb6scEE&list=PL0b6OzIxLPbyrzCMJOFzLnf_-_5E_dkzs&index=143)  
- *Database System Concepts* (7th Edition) – db-book.com  
- [TutorialsPoint SQL Tutorial](https://www.tutorialspoint.com/sql/index.html)  

---

## 👥 Contributors
- **Muhammad Abdul Kayum**  
- **Ittihadur Rahman**  
- **Md. Minhajur Rahman**  
