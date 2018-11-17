<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use bnjns\GoogleServices\Client;
use bnjns\GoogleServices\Services\Drive\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageController extends Controller
{
    const ROOT_FOLDER_ID = '1HDHNvy2Cx3cnq7UoMuFbb-cT0as_1BRh';

    /**
     * Display an index of all image albums.
     *
     * @param \bnjns\GoogleServices\Client $client
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return $this
     */
    public function index(Client $client)
    {
        try {
            $driveService = $client->makeDriveService();
            $albums       = $driveService->getFolderContents(self::ROOT_FOLDER_ID)->getFolders();

            return view('media.images.index')->with('albums', $albums);
        } catch (\Google_Service_Exception $exception) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * View an individual image.
     *
     * @param                              $imageId
     * @param \bnjns\GoogleServices\Client $client
     *
     * @return \Illuminate\Http\Response
     */
    public function view($imageId, Client $client)
    {
        try {
            $driveService = $client->makeDriveService();

            $response = $driveService->getFile($imageId);

            return response($response->getBody()->getContents(), 200, $response->getHeaders());
        } catch (\Google_Service_Exception $exception) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * View an album.
     *
     * @param                                               $albumId
     * @param \bnjns\GoogleServices\Client                  $client
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return $this
     */
    public function album($albumId, Client $client)
    {
        $driveService = $client->makeDriveService();

        try {
            $album = $driveService->getFolderContents(self::ROOT_FOLDER_ID)->filter(function (File $file) use ($albumId) {
                return $file->getId() === $albumId;
            })->first();

            if (is_null($album)) {
                throw new NotFoundHttpException();
            }

            $images = $driveService->getFolderContents($albumId)->getFiles();

            return view('media.images.album')->with([
                'album'  => $album,
                'photos' => $images,
            ]);
        } catch (\Google_Service_Exception $exception) {
            throw new NotFoundHttpException();
        }
    }
}
