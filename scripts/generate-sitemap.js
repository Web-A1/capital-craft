#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Конфигурация сайта
const SITE_CONFIG = {
  baseUrl: 'https://capital-craft.ru',
  pages: [
    {
      path: '/',
      priority: '1.0',
      changefreq: 'daily'
    },
    {
      path: '/faq',
      priority: '0.9',
      changefreq: 'daily'
    }
  ]
};

// Генерируем текущую дату в формате YYYY-MM-DD
function getCurrentDate() {
  const now = new Date();
  return now.toISOString().split('T')[0];
}

// Генерируем XML sitemap
function generateSitemapXML() {
  const currentDate = getCurrentDate();
  
  let xml = '<?xml version="1.0" encoding="UTF-8"?>\n';
  xml += '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n';
  
  SITE_CONFIG.pages.forEach(page => {
    xml += '  <url>\n';
    xml += `    <loc>${SITE_CONFIG.baseUrl}${page.path}</loc>\n`;
    xml += `    <lastmod>${currentDate}</lastmod>\n`;
    xml += `    <changefreq>${page.changefreq}</changefreq>\n`;
    xml += `    <priority>${page.priority}</priority>\n`;
    xml += '  </url>\n';
  });
  
  xml += '</urlset>';
  
  return xml;
}

// Основная функция
function main() {
  try {
    const sitemapContent = generateSitemapXML();
    const sitemapPath = path.join(__dirname, '..', 'sitemap.xml');
    
    // Записываем новый sitemap
    fs.writeFileSync(sitemapPath, sitemapContent, 'utf8');
    
    console.log('✅ Sitemap.xml успешно обновлен!');
    console.log(`📅 Дата обновления: ${getCurrentDate()}`);
    console.log(`📁 Файл сохранен: ${sitemapPath}`);
    
    // Выводим содержимое для проверки
    console.log('\n📋 Содержимое sitemap:');
    console.log(sitemapContent);
    
  } catch (error) {
    console.error('❌ Ошибка при генерации sitemap:', error.message);
    process.exit(1);
  }
}

// Запускаем скрипт
if (require.main === module) {
  main();
}

module.exports = { generateSitemapXML, getCurrentDate };
