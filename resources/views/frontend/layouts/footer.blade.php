 <footer>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
                <!-- Custom JS -->
                <script src="{{asset('frontend/js/script.js')}}"></script>
                
                @if(session('success'))
                <script>
                    alert("{{ session('success') }}");
                </script>
                @endif
                
                @if(session('error'))
                <script>
                    alert("{{ session('error') }}");
                </script>
                @endif
                
                @if(session('info'))
                <script>
                    alert("{{ session('info') }}");
                </script>
                @endif
 </footer>
</body>
</html>