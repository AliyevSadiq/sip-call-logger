<?php



namespace App\Virtual\Request\CallEvent;

/**
 * @OA\Schema(
 *      title="Call Event request",
 *      description="Call Event request body data",
 *      type="object",
 *      required={"call_id", "from","to","event_type","timestamp"}
 * )
 */
class CallEventRequest
{


    /**
     * @OA\Property(
     *      title="call_id",
     *      description="Call id",
     *      example="445436456"
     * )
     *
     * @var string
     */
    public string $call_id;

    /**
     * @OA\Property(
     *      title="from",
     *      description="from number",
     *      example="994550000000"
     * )
     *
     * @var string
     */
    public string $from;


    /**
     * @OA\Property(
     *      title="to",
     *      description="to number",
     *      example="994550000000"
     * )
     *
     * @var string
     */
    public string $to;

    /**
     * @OA\Property(
     *      title="event_type",
     *      description="event_type",
     *      example="call_ended"
     * )
     *
     * @var string
     */
    public string $event_type;


    /**
     * @OA\Property(
     *      title="timestamp",
     *      description="timestamp",
     *      example="2025-10-11 11:12"
     * )
     *
     * @var string
     */
    public string $timestamp;


    /**
     * @OA\Property(
     *      title="duration",
     *      description="duration",
     *      example="10"
     * )
     *
     * @var ?int
     */
    public ?int $duration = null;

}
