# Makefile


CC = gcc
CCFLAGS = -Wall -pedantic -I/usr/include/libxml2
CXX = g++
CXXFLAGS = $(CCFLAGS)
LD = $(CXX) $(CXXFLAGS)


SOURCES = $(wildcard *.hpp *.cpp)

OBJECTS = $(filter %.o,$(SOURCES:.cpp=.o))

DEPENDENCIES = $(OBJECTS:.o=.d)


all: $(OBJECTS)


%.o: %.cpp
	$(CXX) $(CXXFLAGS) -MMD -MP -MT '$*.o $*.d' -c $<

%.d: %.cpp
	$(CXX) $(CXXFLAGS) -MM -MP -MT '$*.o $*.d' -c $< > $@


clean:
	rm -f *.d
	rm -f *.o


.PHONY: all clean


-include $(DEPENDENCIES)
