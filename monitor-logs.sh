#!/bin/bash

# Monitor Laravel logs in real-time for upload debugging
echo "Monitoring Laravel logs for upload activity..."
echo "Press Ctrl+C to stop monitoring"
echo ""

tail -f storage/logs/laravel.log | grep -E "(Photo upload|upload|ERROR|Exception)"



