#!/usr/bin/env python3
"""
Laravel Complete Project File Collector
Collects all important files: Controllers, Models, Views, Migrations, 
Middleware, Services, Listeners, Routes, Config, JS, CSS, etc.
"""

import os
import sys
from pathlib import Path
from datetime import datetime
import fnmatch

class LaravelCompleteCollector:
    def __init__(self, project_path):
        self.project_path = Path(project_path).resolve()
        self.output = []
        self.file_count = 0
        self.skip_dirs = {
            'node_modules', 'vendor', 'storage', 'cache', 'bootstrap/cache',
            '.git', '.idea', '.vscode', 'debugbar', 'sessions',
            'framework', 'logs', 'pail'
        }
        
    def should_skip_dir(self, path):
        """Check if directory should be skipped"""
        parts = Path(path).parts
        for skip in self.skip_dirs:
            if skip in parts:
                return True
        return False
    
    def collect_files(self):
        """Collect all relevant Laravel files"""
        
        # Define file collections with patterns
        collections = {
            '📁 CONTROLLERS': {
                'paths': ['app/Http/Controllers', 'app/Http/Controllers/*'],
                'patterns': ['*.php'],
                'description': 'All HTTP controllers'
            },
            '📁 MODELS': {
                'paths': ['app/Models'],
                'patterns': ['*.php'],
                'description': 'All Eloquent models'
            },
            '📁 VIEWS (Blade Templates)': {
                'paths': [
                    'resources/views',
                    'resources/views/admin',
                    'resources/views/admin/customers',
                    'resources/views/admin/finances',
                    'resources/views/admin/orders',
                    'resources/views/admin/prices',
                    'resources/views/admin/riders',
                    'resources/views/admin/vendors',
                    'resources/views/auth',
                    'resources/views/auth/passwords',
                    'resources/views/components',
                    'resources/views/customer',
                    'resources/views/customer/cart',
                    'resources/views/customer/orders',
                    'resources/views/customer/products',
                    'resources/views/layouts',
                    'resources/views/layouts/components',
                    'resources/views/profile',
                    'resources/views/rider',
                    'resources/views/vendor',
                    'resources/views/vendor/orders',
                    'resources/views/vendor/products',
                    'resources/views/vendor/profile'
                ],
                'patterns': ['*.blade.php', '*.php'],
                'description': 'All Blade view templates'
            },
            '📁 MIGRATIONS': {
                'paths': ['database/migrations'],
                'patterns': ['*.php'],
                'description': 'Database migration files'
            },
            '📁 SEEDERS & FACTORIES': {
                'paths': ['database/seeders', 'database/factories'],
                'patterns': ['*.php'],
                'description': 'Database seeders and factories'
            },
            '📁 MIDDLEWARE': {
                'paths': ['app/Http/Middleware'],
                'patterns': ['*.php'],
                'description': 'HTTP middleware'
            },
            '📁 REQUESTS (Form Requests)': {
                'paths': ['app/Http/Requests'],
                'patterns': ['*.php'],
                'description': 'Form request validation'
            },
            '📁 SERVICES': {
                'paths': ['app/Services'],
                'patterns': ['*.php'],
                'description': 'Business logic services'
            },
            '📁 LISTENERS & EVENTS': {
                'paths': ['app/Listeners', 'app/Events'],
                'patterns': ['*.php'],
                'description': 'Event listeners and events'
            },
            '📁 PROVIDERS': {
                'paths': ['app/Providers'],
                'patterns': ['*.php'],
                'description': 'Service providers'
            },
            '📁 ROUTES': {
                'paths': ['routes'],
                'patterns': ['*.php'],
                'description': 'Route definitions'
            },
            '📁 CONFIGURATION': {
                'paths': ['config'],
                'patterns': ['*.php'],
                'description': 'Laravel config files'
            },
            '📁 JAVASCRIPT': {
                'paths': ['resources/js', 'resources/js/*'],
                'patterns': ['*.js', '*.jsx', '*.ts', '*.tsx', '*.vue'],
                'description': 'JavaScript/Vue/React files'
            },
            '📁 CSS/SCSS': {
                'paths': ['resources/css', 'resources/sass', 'resources/scss'],
                'patterns': ['*.css', '*.scss', '*.sass', '*.less'],
                'description': 'Stylesheet files'
            },
            '📁 DATABASE (Additional)': {
                'paths': ['database'],
                'patterns': ['*.sql', '*.json'],
                'description': 'SQL and JSON database files'
            },
            '📁 PUBLIC ASSETS': {
                'paths': ['public'],
                'patterns': ['*.js', '*.css', '*.png', '*.jpg', '*.jpeg', '*.svg', '*.ico'],
                'description': 'Public assets'
            },
            '📁 LANG (Translations)': {
                'paths': ['lang', 'resources/lang'],
                'patterns': ['*.php', '*.json'],
                'description': 'Language/translation files'
            }
        }
        
        # Add header
        self.add_header()
        
        # Process each collection
        for category, config in collections.items():
            print(f"\n📂 Processing {category}...")
            self.output.append(f"\n\n{'='*80}")
            self.output.append(f"\n{category}")
            self.output.append(f"\n{'='*80}")
            self.output.append(f"\n// {config['description']}\n")
            
            files_found = False
            
            for path_pattern in config['paths']:
                full_path = self.project_path / path_pattern
                
                # Handle wildcards in path
                if '*' in path_pattern:
                    base_dir = self.project_path / Path(path_pattern).parent
                    if base_dir.exists():
                        for subdir in base_dir.iterdir():
                            if subdir.is_dir() and not self.should_skip_dir(subdir):
                                self.scan_directory(subdir, config['patterns'])
                                files_found = True
                else:
                    if full_path.exists() and not self.should_skip_dir(full_path):
                        if full_path.is_dir():
                            self.scan_directory(full_path, config['patterns'])
                            files_found = True
                        elif full_path.is_file():
                            self.add_file_content(full_path)
                            files_found = True
            
            if not files_found:
                self.output.append(f"\n// No files found in this category\n")
                print(f"   ⚠️  No files found")
        
        # Add additional important files
        self.add_additional_files()
        
        return '\n'.join(self.output)
    
    def scan_directory(self, directory, extensions):
        """Recursively scan directory for files"""
        try:
            for item in sorted(directory.iterdir()):
                if self.should_skip_dir(item):
                    continue
                    
                if item.is_file():
                    # Check file extension
                    if any(fnmatch.fnmatch(item.name, pattern) for pattern in extensions):
                        self.add_file_content(item)
                elif item.is_dir():
                    self.scan_directory(item, extensions)
        except PermissionError:
            print(f"   🔒 Permission denied: {directory}")
        except Exception as e:
            print(f"   ⚠️  Error scanning {directory}: {e}")
    
    def add_file_content(self, file_path):
        """Add file content to output"""
        try:
            relative_path = file_path.relative_to(self.project_path)
            content = file_path.read_text(encoding='utf-8')
            
            # Add file separator and info
            self.output.append(f"\n{'─'*80}")
            self.output.append(f"📄 File: {relative_path}")
            self.output.append(f"📏 Size: {len(content)} bytes")
            self.output.append(f"{'─'*80}\n")
            self.output.append(content)
            
            self.file_count += 1
            if self.file_count % 50 == 0:
                print(f"   📄 Collected {self.file_count} files so far...")
            
        except UnicodeDecodeError:
            # Skip binary files
            pass
        except Exception as e:
            print(f"   ⚠️  Error reading {file_path}: {e}")
    
    def add_additional_files(self):
        """Add additional important files that might be missed"""
        additional_files = [
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
            'vite.config.js',
            'webpack.mix.js',
            'phpunit.xml',
            '.env.example',
            '.env',
            'artisan',
            'README.md',
            'README_ALI_SAFI.md'
        ]
        
        self.output.append(f"\n\n{'='*80}")
        self.output.append(f"\n📁 ADDITIONAL FILES")
        self.output.append(f"\n{'='*80}\n")
        
        for file in additional_files:
            full_path = self.project_path / file
            if full_path.exists():
                self.add_file_content(full_path)
    
    def add_header(self):
        """Add header information to output"""
        header = f"""<?php
/**
 * ============================================================================
 * LARAVEL PROJECT COMPLETE EXPORT
 * ============================================================================
 * 
 * Project: {self.project_path.name}
 * Path: {self.project_path}
 * Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
 * 
 * This file contains ALL application files for DeepSeek analysis:
 * - Controllers, Models, Views, Migrations
 * - Middleware, Requests, Services
 * - Events, Listeners, Providers
 * - Routes, Config files
 * - JavaScript, CSS/SCSS files
 * - And more...
 * 
 * Total files collected: Will be counted during processing
 * ============================================================================
 * 
 */
 
"""
        self.output.append(header)
    
    def save_to_file(self, output_text):
        """Save collected content to a file"""
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        output_file = self.project_path / f"laravel_complete_export_{timestamp}.txt"
        
        # Write in chunks to handle large files
        chunk_size = 1024 * 1024  # 1MB chunks
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write(output_text)
        
        file_size = output_file.stat().st_size / (1024 * 1024)  # Size in MB
        print(f"\n💾 Saved to: {output_file}")
        print(f"📦 File size: {file_size:.2f} MB")
        return output_file
    
    def print_summary(self):
        """Print summary of collected files"""
        print(f"\n{'='*60}")
        print(f"📊 COLLECTION SUMMARY")
        print(f"{'='*60}")
        print(f"✅ Total files collected: {self.file_count}")
        print(f"📁 Categories collected: 14+")
        print(f"💾 Output file created with all content")
        print(f"\n💡 Tip: You can now upload the .txt file to DeepSeek")
        print(f"   or open it in any text editor")
        print(f"{'='*60}\n")

def main():
    # Get project path
    if len(sys.argv) > 1:
        project_path = sys.argv[1]
    else:
        project_path = os.getcwd()
    
    # Check if path exists
    if not Path(project_path).exists():
        print(f"❌ Path not found: {project_path}")
        sys.exit(1)
    
    # Check if it's a Laravel project
    if not (Path(project_path) / 'artisan').exists():
        print(f"⚠️  Warning: This doesn't look like a Laravel project root")
        response = input("Continue anyway? (y/n): ").lower()
        if response != 'y':
            sys.exit(0)
    
    print(f"\n{'='*60}")
    print(f"🚀 LARAVEL COMPLETE FILE COLLECTOR")
    print(f"{'='*60}")
    print(f"📁 Project: {Path(project_path).name}")
    print(f"📍 Path: {project_path}")
    print(f"{'='*60}\n")
    
    # Create collector and process files
    collector = LaravelCompleteCollector(project_path)
    
    print("Starting collection... This may take a few minutes.\n")
    collected_content = collector.collect_files()
    
    # Save to file
    output_file = collector.save_to_file(collected_content)
    
    collector.print_summary()
    
    # Ask if user wants to see file location
    print(f"✨ Export complete!")
    print(f"📂 File location: {output_file}")
    print(f"\nYou can now:")
    print(f"   1. Upload '{output_file.name}' to DeepSeek")
    print(f"   2. Open it in any text editor to review")
    print(f"   3. Share it with your team\n")

if __name__ == "__main__":
    main()