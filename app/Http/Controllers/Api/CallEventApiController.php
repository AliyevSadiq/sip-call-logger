<?php

namespace App\Http\Controllers\Api;

use App\CommandBus\API\CallEvent\Commands\CallEventReceiveCommand;
use App\CommandBus\Core\Contract\CommandBusInterface;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Api\CallEventRequest;
use Illuminate\Http\JsonResponse;

class CallEventApiController extends ApiController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }


    /**
     * @OA\Post(
     *      tags={"CallEvent"},
     *      path="/api/call-event",
     *      operationId="call-event",
     *      summary="Call event",
     *      description="Call event",
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema (ref="#/components/schemas/CallEventRequest")
     *         ),
     *         @OA\JsonContent(ref="#/components/schemas/CallEventRequest")
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request",
     *          @OA\JsonContent()
     *       ),
     *     security={{"sanctum":{}}},
     * )
     * @return JsonResponse
     */
    public function receive(CallEventRequest $request): JsonResponse
    {
        try{
            $command=new CallEventReceiveCommand();
            $this->commandBus->dispatch($command,$request->all());

            return response()->json([
                'status'=>'queued'
            ]);
        }catch (\Exception $exception){
            return $this->jsonError($exception->getMessage());
        }
    }

}
