<div align="center">
  <img src="./assets/resume-forge.png" alt="ResumeForge Logo" width="120" height="120">
  <h1>ResumeForge (Free Open-Source Edition)</h1>
  <p><strong>A modular, privacy-first PHP resume builder.</strong></p>
  
  [![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-777BB4?style=flat-square&logo=php)](#)
  [![License](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)](#)
  [![Dependencies](https://img.shields.io/badge/Dependencies-Composer-orange.svg?style=flat-square)](#)

  <p>A beautiful, highly-extensible, zero-database resume builder. Features a 4-step wizard, real-time visual builder, and server-side PDF exports.</p>
</div>

---

## ✨ Features (Core Edition)

- **📄 Native PDF Exports:** Server-side rendering using `mpdf`.
- **📸 PNG Exports:** Client-side fallback generation using `html2canvas`.
- **🎨 Beautiful Templates:** Includes traditional, modern, creative, and strictly ATS-optimized layouts.
- **💾 Shared-Host Friendly:** Uses SQLite (or JSON files as fallback).
- **🧩 Modular Addon Engine:** Extend the core software easily via the `app/addons/` directory.

---

## 💎 Pro Addons (Available Separately)

This repository contains the **Free Open-Source Engine**. The following premium features are available as plug-and-play addons that can be dropped into the `app/addons/` folder:

- **🧠 AI Writer & Importer (`addon-ai-writer`)**: Parse resumes automatically and use Gemini AI to write summaries and polish bullets.
- **📊 ATS Scorer (`addon-ats-scorer`)**: Rank your resume against international ATS standards.
- **📄 DOCX Export (`addon-export-docx`)**: True native Microsoft Word document generation with embedded images.

---

## 🚀 Installation & Setup

1. **Upload & Configure**
   Upload the repository to your web server (make it the web root).
   
2. **Permissions**
   Ensure the following directories are writable:
   ```bash
   chmod 775 uploads uploads/photos uploads/exports app/storage
   ```

3. **Install Dependencies**
   The project requires Composer to handle export libraries. Run:
   ```bash
   composer install
   ```

---

## 🔒 Security

- **Directory Protection:** The `app/` directory is blocked from direct web access via `.htaccess`.
- **Upload Validation:** strict extension whitelists and image probing to prevent shell uploads.
- **SQLi Protection:** Uses PDO Prepared Statements exclusively.

---

## 👤 Author & Developer

* **Amanullah Khan**
* **Role:** Developer & Maintainer
* **Location:** Pakistan
* **GitHub:** [GitHub Profile](https://github.com/amanullahykhan)
* **LinkedIn:** [LinkedIn](https://www.linkedin.com/in/amanullahykhan/)

---

<div align="center">
  <i>Built with modern PHP. Fast, secure, and easily extensible.</i>
</div>