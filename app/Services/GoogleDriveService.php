<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class GoogleDriveService
{
    private $client;
    private $service;
    private $folderId;
    
    public function __construct()
    {
        $this->initializeClient();
    }
    
    /**
     * Initialize Google Drive client using OAuth tokens
     */
    private function initializeClient()
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName('Visitor Management CRM');
            $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
            $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            // Auto-detect redirect URI based on environment
            $redirectUri = env('APP_ENV') === 'local' 
                ? env('GOOGLE_REDIRECT_URI_LOCAL', 'http://localhost:8000/auth/google/callback')
                : env('GOOGLE_REDIRECT_URI_PROD', 'https://motionagra.com/auth/google/callback');
            $this->client->setRedirectUri($redirectUri);
            $this->client->addScope(Drive::DRIVE_FILE);
            
            // Get stored OAuth tokens
            $tokenRecord = \Illuminate\Support\Facades\DB::table('google_drive_tokens')
                ->orderBy('updated_at', 'desc')
                ->first();
            
            if (!$tokenRecord) {
                throw new \Exception('Google Drive not authorized. Admin needs to authorize first.');
            }
            
            // Check if token is expired and refresh if needed
            if (now()->gt($tokenRecord->expires_at)) {
                $this->refreshAccessToken($tokenRecord);
            } else {
                $this->client->setAccessToken($tokenRecord->access_token);
            }
            
            $this->service = new Drive($this->client);
            $this->folderId = env('GOOGLE_DRIVE_FOLDER_ID');
            
            if (!$this->folderId) {
                throw new \Exception('Google Drive folder ID not configured');
            }
            
        } catch (\Exception $e) {
            Log::error('Google Drive initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Refresh expired access token
     */
    private function refreshAccessToken($tokenRecord)
    {
        try {
            $this->client->refreshToken($tokenRecord->refresh_token);
            $newToken = $this->client->getAccessToken();
            
            // Update stored tokens
            \Illuminate\Support\Facades\DB::table('google_drive_tokens')
                ->where('id', $tokenRecord->id)
                ->update([
                    'access_token' => $newToken['access_token'],
                    'expires_in' => $newToken['expires_in'],
                    'expires_at' => now()->addSeconds($newToken['expires_in']),
                    'updated_at' => now(),
                ]);
                
        } catch (\Exception $e) {
            Log::error('Token refresh failed: ' . $e->getMessage());
            throw new \Exception('Google Drive authorization expired. Admin needs to reauthorize.');
        }
    }
    
    /**
     * Upload file to Google Drive
     */
    public function uploadFile(UploadedFile $file, $interactionId)
    {
        try {
            // Generate unique filename
            $timestamp = now()->format('Y-m-d_H-i-s');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $uniqueName = "interaction_{$interactionId}_{$timestamp}_{$originalName}.{$extension}";
            
            // Create Google Drive file metadata
            $fileMetadata = new DriveFile([
                'name' => $uniqueName,
                'parents' => [$this->folderId]
            ]);
            
            // Upload file to Google Drive
            $content = file_get_contents($file->getPathname());
            $result = $this->service->files->create(
                $fileMetadata,
                [
                    'data' => $content,
                    'mimeType' => $file->getMimeType(),
                    'uploadType' => 'multipart'
                ]
            );
            
            // Make file publicly viewable (optional - for easy access)
            $this->makeFilePublic($result->getId());
            
            return [
                'google_drive_file_id' => $result->getId(),
                'google_drive_url' => "https://drive.google.com/file/d/{$result->getId()}/view",
                'download_url' => "https://drive.google.com/uc?id={$result->getId()}&export=download"
            ];
            
        } catch (\Exception $e) {
            Log::error('Google Drive upload failed: ' . $e->getMessage());
            throw new \Exception('Failed to upload file to Google Drive: ' . $e->getMessage());
        }
    }
    
    /**
     * Make file publicly viewable
     */
    private function makeFilePublic($fileId)
    {
        try {
            $permission = new \Google\Service\Drive\Permission([
                'type' => 'anyone',
                'role' => 'reader'
            ]);
            
            $this->service->permissions->create($fileId, $permission);
        } catch (\Exception $e) {
            Log::warning('Could not make file public: ' . $e->getMessage());
            // Don't throw error - file is still uploaded, just not public
        }
    }
    
    /**
     * Delete file from Google Drive
     */
    public function deleteFile($fileId)
    {
        try {
            $this->service->files->delete($fileId);
            return true;
        } catch (\Exception $e) {
            Log::error('Google Drive delete failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get file information from Google Drive
     */
    public function getFileInfo($fileId)
    {
        try {
            return $this->service->files->get($fileId);
        } catch (\Exception $e) {
            Log::error('Google Drive file info failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate file type and size
     */
    public function validateFile(UploadedFile $file)
    {
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'mp3', 'wav'];
        $maxSizes = [
            'pdf' => 5 * 1024 * 1024,      // 5MB
            'jpg' => 2 * 1024 * 1024,      // 2MB
            'jpeg' => 2 * 1024 * 1024,     // 2MB
            'png' => 2 * 1024 * 1024,      // 2MB
            'webp' => 2 * 1024 * 1024,     // 2MB
            'mp3' => 10 * 1024 * 1024,     // 10MB
            'wav' => 10 * 1024 * 1024,     // 10MB
        ];
        
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Check file type
        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
        }
        
        // Check file size
        if ($file->getSize() > $maxSizes[$extension]) {
            $maxSizeMB = $maxSizes[$extension] / (1024 * 1024);
            throw new \Exception("File too large. Maximum size for {$extension} files: {$maxSizeMB}MB");
        }
        
        return true;
    }
    
    /**
     * Upload file from server path to Google Drive
     */
    public function uploadFileFromPath($filePath, $originalFilename, $interactionId = null)
    {
        try {
            if (!file_exists($filePath)) {
                throw new \Exception('File not found on server');
            }
            
            $this->service = new Drive($this->client);
            
            // Create folder structure if needed
            $folderId = $this->getOrCreateFolder($interactionId);
            
            // Prepare file metadata
            $fileMetadata = new DriveFile([
                'name' => $originalFilename,
                'parents' => [$folderId]
            ]);
            
            // Upload file
            $content = file_get_contents($filePath);
            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => mime_content_type($filePath),
                'uploadType' => 'multipart',
                'fields' => 'id,name,webViewLink,webContentLink'
            ]);
            
            return [
                'google_drive_file_id' => $file->getId(),
                'google_drive_url' => $file->getWebViewLink(),
                'download_url' => $file->getWebContentLink()
            ];
            
        } catch (\Exception $e) {
            Log::error('Google Drive upload from path error: ' . $e->getMessage());
            throw new \Exception('Failed to upload file to Google Drive: ' . $e->getMessage());
        }
    }
}
